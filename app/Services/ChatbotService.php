<?php

namespace App\Services;

use App\Models\ChatbotKnowledge;
use App\Models\ChatbotQuery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fan Hub+ assistant.
 *
 * Two answer paths:
 *  1. Knowledge base — chatbot_knowledge rows are scored in PHP by keyword
 *     overlap + question similarity (strong threshold, then a soft fallback).
 *     A match is always the safety net when the AI provider is unavailable.
 *  2. AI provider — when config('ai.enabled') is truthy and an API key is
 *     configured, the message (with knowledge context + recent history) is
 *     sent to the OpenAI-compatible or Anthropic endpoint.
 *
 * Every exchange is persisted to chatbot_queries. Conversation history comes
 * from the incoming request (API), the session (web) or the database — merged
 * into the returned conversation and capped at 40 entries.
 */
class ChatbotService
{
    /** Strong match: confident answer from the knowledge base. */
    private const STRONG_THRESHOLD = 0.45;

    /** Soft match: close enough to still answer with a knowledge entry. */
    private const SOFT_THRESHOLD = 0.25;

    /** Session keys the web chat widget may store history under. */
    private const SESSION_KEYS = ['chat.conversation', 'chatbot.conversation', 'chat_history', 'chatbot.history'];

    private const MAX_CONVERSATION = 40;

    /**
     * Answer a message and return the reply plus the merged conversation.
     *
     * @return array{reply: string, conversation: list<array{role: string, content: string, created_at: string}>}
     */
    public function handle(string $message, ?int $userId, string $sessionId): array
    {
        $message = $this->clean($message);
        $history = $this->resolveHistory($sessionId);

        if ($message === '') {
            return [
                'reply' => 'Could you rephrase that for me? I did not catch a question.',
                'conversation' => $history,
            ];
        }

        $knowledge = $this->matchKnowledge($message);
        $reply = $this->callProvider($message, $history, $knowledge);

        if ($reply === null) {
            $reply = $knowledge !== null && trim($knowledge->answer) !== ''
                ? $knowledge->answer
                : $this->offlineReply();
        }

        try {
            ChatbotQuery::query()->create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'message' => $message,
                'response' => $reply,
                'knowledge_id' => $knowledge?->id,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Chatbot query logging failed: '.$exception->getMessage());
        }

        $conversation = $history;
        $conversation[] = [
            'role' => 'user',
            'content' => $message,
            'created_at' => now()->toIso8601String(),
        ];
        $conversation[] = [
            'role' => 'assistant',
            'content' => $reply,
            'created_at' => now()->toIso8601String(),
        ];

        return [
            'reply' => $reply,
            'conversation' => array_slice($conversation, -self::MAX_CONVERSATION),
        ];
    }

    /**
     * Prior conversation for a session, replayed from chatbot_queries.
     *
     * @return list<array{role: string, content: string, created_at: string}>
     */
    public function history(string $sessionId): array
    {
        if ($sessionId === '') {
            return [];
        }

        try {
            $rows = ChatbotQuery::query()
                ->where('session_id', $sessionId)
                ->orderBy('id')
                ->get(['id', 'message', 'response', 'created_at']);
        } catch (\Throwable $exception) {
            Log::warning('Chatbot history lookup failed: '.$exception->getMessage());

            return [];
        }

        $conversation = [];

        foreach ($rows as $row) {
            $createdAt = $row->created_at?->toIso8601String() ?? now()->toIso8601String();

            if (trim((string) $row->message) !== '') {
                $conversation[] = ['role' => 'user', 'content' => (string) $row->message, 'created_at' => $createdAt];
            }

            if (trim((string) $row->response) !== '') {
                $conversation[] = ['role' => 'assistant', 'content' => (string) $row->response, 'created_at' => $createdAt];
            }
        }

        return array_slice($conversation, -self::MAX_CONVERSATION);
    }

    /*
    |--------------------------------------------------------------------------
    | History resolution
    |--------------------------------------------------------------------------
    */

    /**
     * History supplied by the request (API), the session (web) or the database.
     *
     * @return list<array{role: string, content: string, created_at: string}>
     */
    private function resolveHistory(string $sessionId): array
    {
        try {
            $fromRequest = request()->input('conversation');

            if (is_array($fromRequest) && $fromRequest !== []) {
                return $this->normaliseHistory($fromRequest);
            }

            foreach (self::SESSION_KEYS as $key) {
                $stored = session()->get($key);

                if (is_array($stored) && $stored !== []) {
                    return $this->normaliseHistory($stored);
                }
            }
        } catch (\Throwable $exception) {
            // No HTTP/session context (console, queue) — fall back to the database.
        }

        return $this->history($sessionId);
    }

    /**
     * Coerce an arbitrary conversation payload to well-formed entries.
     *
     * @param  array<mixed>  $entries
     * @return list<array{role: string, content: string, created_at: string}>
     */
    private function normaliseHistory(array $entries): array
    {
        $conversation = [];

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $role = $entry['role'] ?? null;
            $content = $entry['content'] ?? null;

            if (! is_string($content) || trim($content) === '' || ! in_array($role, ['user', 'assistant'], true)) {
                continue;
            }

            $conversation[] = [
                'role' => $role,
                'content' => $content,
                'created_at' => is_string($entry['created_at'] ?? null) ? $entry['created_at'] : now()->toIso8601String(),
            ];
        }

        return array_slice($conversation, -self::MAX_CONVERSATION);
    }

    /*
    |--------------------------------------------------------------------------
    | Knowledge base matching
    |--------------------------------------------------------------------------
    */

    /**
     * Best knowledge entry for a message, or null when nothing is close.
     */
    private function matchKnowledge(string $message): ?ChatbotKnowledge
    {
        try {
            $entries = ChatbotKnowledge::query()->active()->get();
        } catch (\Throwable $exception) {
            Log::warning('Chatbot knowledge lookup failed: '.$exception->getMessage());

            return null;
        }

        if ($entries->isEmpty()) {
            return null;
        }

        $messageTokens = $this->tokens($message);
        $messageNormalised = $this->normalise($message);

        if ($messageTokens === []) {
            return null;
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($entries as $entry) {
            $score = $this->score($entry, $messageTokens, $messageNormalised);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $entry;
            }

            if ($bestScore >= self::STRONG_THRESHOLD) {
                return $best;
            }
        }

        if ($bestScore >= self::SOFT_THRESHOLD) {
            return $best;
        }

        return null;
    }

    /**
     * Score one knowledge entry against the message.
     *
     * Weighted blend of: question-token overlap, Jaccard similarity,
     * curated keyword hits and full-string similarity_text.
     */
    private function score(ChatbotKnowledge $entry, array $messageTokens, string $messageNormalised): float
    {
        $questionTokens = array_unique($this->tokens($entry->question));
        $keywordTokens = array_unique($this->tokens((string) $entry->keywords));
        $messageSet = array_unique($messageTokens);

        if ($questionTokens === []) {
            return 0.0;
        }

        $intersection = count(array_intersect($messageSet, $questionTokens));
        $tokenScore = $intersection / max(1, count($questionTokens));
        $jaccard = $intersection / max(1, count(array_unique(array_merge($messageSet, $questionTokens))));

        $kwScore = 0.0;
        if ($keywordTokens !== []) {
            $hits = count(array_intersect($messageSet, $keywordTokens));
            $kwScore = min(1.0, $hits / max(1, min(count($keywordTokens), 3)));
        }

        similar_text($messageNormalised, $this->normalise($entry->question), $percent);
        $textScore = $percent / 100;

        return (0.40 * $tokenScore) + (0.20 * $jaccard) + (0.30 * $kwScore) + (0.10 * $textScore);
    }

    /*
    |--------------------------------------------------------------------------
    | AI provider
    |--------------------------------------------------------------------------
    */

    /**
     * Call the configured provider; null on any failure (caller falls back).
     */
    private function callProvider(string $message, array $history, ?ChatbotKnowledge $knowledge): ?string
    {
        if (! config('ai.enabled') || ! filled(config('ai.api_key'))) {
            return null;
        }

        $provider = (string) config('ai.provider', 'openai');
        $system = $this->systemPrompt($knowledge);

        $recent = [];
        foreach (array_slice($history, -10) as $entry) {
            $recent[] = ['role' => $entry['role'], 'content' => $entry['content']];
        }
        $recent[] = ['role' => 'user', 'content' => $message];

        try {
            if ($provider === 'anthropic') {
                $response = Http::withHeaders([
                    'x-api-key' => (string) config('ai.api_key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])->timeout((int) config('ai.timeout', 20))->post('https://api.anthropic.com/v1/messages', [
                    'model' => (string) config('ai.model', 'claude-3-5-sonnet-latest'),
                    'max_tokens' => (int) config('ai.max_tokens', 512),
                    'temperature' => (float) config('ai.temperature', 0.7),
                    'system' => $system,
                    'messages' => $recent,
                ]);
            } else {
                $response = Http::timeout((int) config('ai.timeout', 20))->post(
                    rtrim((string) config('ai.base_url', 'https://api.openai.com/v1'), '/').'/chat/completions',
                    [
                        'model' => (string) config('ai.model', 'gpt-4o-mini'),
                        'max_tokens' => (int) config('ai.max_tokens', 512),
                        'temperature' => (float) config('ai.temperature', 0.7),
                        'messages' => array_merge([['role' => 'system', 'content' => $system]], $recent),
                    ]
                );
            }

            $response->throw();

            $text = $provider === 'anthropic'
                ? ($response->json('content.0.text') ?? null)
                : ($response->json('choices.0.message.content') ?? null);

            if (is_string($text) && trim($text) !== '') {
                return trim($text);
            }

            return null;
        } catch (\Throwable $exception) {
            Log::warning('AI provider call failed: '.$exception->getMessage());

            return null;
        }
    }

    /**
     * System prompt describing Fan Hub+ plus the matched knowledge entry.
     */
    private function systemPrompt(?ChatbotKnowledge $knowledge): string
    {
        $prompt = <<<'PROMPT'
You are the Fan Hub+ assistant inside FAN HUB+ (Fan Hub+), a premium dark-themed
fandom platform covering Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga and Cosplay.

What members can do on the site:
- Browse Home (hero, trending, featured characters, latest articles, multimedia, events,
  releases, merchandise), Explore, and each category page (/category/{slug}).
- Open detail pages for titles, characters, articles, multimedia, events, merchandise
  and upcoming releases; use the search bar and the filter/sort panels on listings.
- Create an account, bookmark items with personal notes (bookmark buttons on cards;
  saved items appear under Dashboard and Bookmarks), rate items from 1 to 10,
  and follow events and releases.
- Adjust Settings (theme, font scale, reduced motion, notifications), submit fan content
  for moderator review, send feedback, and read notifications.

Merchandise policy: Fan Hub+ only showcases merchandise. We never sell, ship or take
payment for products — there is no checkout. Point users to the product page and official
stores instead.

Guidelines: be concise (about 120 words maximum), warm and practical; give concrete
page names and steps; for fandom questions, suggest Explore or the category pages;
never invent prices, dates or stock; if unsure, say so and suggest Explore, Events,
Bookmarks or the Contact page.
PROMPT;

        if ($knowledge !== null) {
            $prompt .= "\n\nRelevant knowledge base entry (prefer this answer when it fits):\n"
                ."Q: {$knowledge->question}\nA: {$knowledge->answer}";
        }

        return $prompt;
    }

    /**
     * Offline answer used when nothing matched and no provider answered.
     */
    private function offlineReply(): string
    {
        return 'I am running in offline mode right now, but I can help with fandoms, '
            ."events, bookmarks, releases, merchandise and your account.\n\n"
            ."Try asking me:\n"
            .'- How do bookmarks work?'."\n"
            .'- What events are coming up?'."\n"
            .'- How do I submit my fan content?';
    }

    /*
    |--------------------------------------------------------------------------
    | Text helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Collapse whitespace and trim a raw message.
     */
    private function clean(string $text): string
    {
        $text = (string) preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    /**
     * Lowercase, strip punctuation, collapse whitespace.
     */
    private function normalise(string $text): string
    {
        $text = mb_strtolower($text);
        $text = (string) preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Tokenise a message: stopwords removed, naive plural stemming applied.
     *
     * @return list<string>
     */
    private function tokens(string $text): array
    {
        $normalised = $this->normalise($text);

        if ($normalised === '') {
            return [];
        }

        $tokens = [];

        foreach (explode(' ', $normalised) as $word) {
            if ($word === '' || strlen($word) < 2 || in_array($word, self::STOPWORDS, true)) {
                continue;
            }

            if (strlen($word) > 3 && str_ends_with($word, 's') && ! str_ends_with($word, 'ss')) {
                $word = substr($word, 0, -1);
            }

            $tokens[] = $word;
        }

        return array_values(array_unique($tokens));
    }

    /**
     * Common English stopwords stripped before scoring.
     *
     * @var list<string>
     */
    private const STOPWORDS = [
        'the', 'a', 'an', 'to', 'of', 'in', 'on', 'for', 'and', 'or', 'is', 'are', 'was',
        'were', 'be', 'been', 'i', 'you', 'your', 'my', 'me', 'we', 'us', 'it', 'its',
        'this', 'that', 'with', 'at', 'by', 'from', 'as', 'do', 'does', 'did', 'can',
        'could', 'would', 'should', 'will', 'shall', 'have', 'has', 'had', 'what', 'which',
        'who', 'whom', 'how', 'when', 'where', 'why', 'if', 'then', 'than', 'so', 'not',
        'no', 'but', 'about', 'into', 'there', 'here', 'they', 'them', 'their', 'he', 'she',
        'his', 'her', 'am', 'get', 'got', 'like', 'just', 'please', 'tell', 'show', 'need',
        'want', 'make', 'much', 'many', 'more', 'some', 'any', 'all', 'out', 'up',
    ];
}
