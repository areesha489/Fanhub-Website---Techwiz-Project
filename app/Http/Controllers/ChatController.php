<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Chat widget endpoints. The answer itself comes from B5's ChatbotService;
 * when that service is unavailable a local canned reply keeps the widget alive.
 *
 * POST /chat · GET /chat/history
 */
class ChatController extends Controller
{
    /**
     * Maximum number of chat messages kept in the session.
     */
    private const HISTORY_LIMIT = 40;

    /**
     * Answer one message and return the conversation so far.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'session_id' => ['nullable', 'string', 'max:64'],
        ]);

        $message = trim((string) $validated['message']);
        $sessionId = (string) ($validated['session_id'] ?? '');

        if ($sessionId === '') {
            $sessionId = (string) Str::uuid();
        }

        $userId = $request->user()?->getKey();

        $history = is_array(session('chat_history')) ? session('chat_history') : [];
        $history[] = [
            'role' => 'user',
            'content' => $message,
            'created_at' => now()->toIso8601String(),
        ];

        $serviceConversation = null;
        $reply = null;

        try {
            if (class_exists(ChatbotService::class) && method_exists(ChatbotService::class, 'handle')) {
                /** @var array{reply?: mixed, conversation?: mixed} $result */
                $result = app(ChatbotService::class)->handle($message, $userId, $sessionId);

                if (is_array($result)) {
                    $reply = is_string($result['reply'] ?? null) ? $result['reply'] : null;
                    $serviceConversation = is_array($result['conversation'] ?? null) ? $result['conversation'] : null;
                }
            }
        } catch (\Throwable $exception) {
            Log::warning('Chatbot service failed: '.$exception->getMessage());
        }

        if ($reply === null || trim($reply) === '') {
            $reply = $this->fallbackReply($message);
        }

        $history[] = [
            'role' => 'assistant',
            'content' => $reply,
            'created_at' => now()->toIso8601String(),
        ];

        if (count($history) > self::HISTORY_LIMIT) {
            $history = array_slice($history, -self::HISTORY_LIMIT);
        }

        session(['chat_history' => $history]);
        session(['chat_session_id' => $sessionId]);

        return response()->json([
            'session_id' => $sessionId,
            'reply' => $reply,
            'conversation' => $this->conversationForClient($serviceConversation ?? $history),
        ]);
    }

    /**
     * Conversation of the current session.
     */
    public function history(Request $request): JsonResponse
    {
        $history = is_array(session('chat_history')) ? session('chat_history') : [];

        return response()->json([
            'session_id' => (string) session('chat_session_id', ''),
            'conversation' => $history,
        ]);
    }

    /**
     * assets/js/app.js renders `reply` itself, so the payload must not end on an
     * assistant turn (it would skip the render and look like a dead chat).
     *
     * @param  array<mixed>  $conversation
     * @return array<mixed>
     */
    private function conversationForClient(array $conversation): array
    {
        $conversation = array_values($conversation);

        $last = end($conversation);

        if ($last !== false && is_array($last) && ($last['role'] ?? null) === 'assistant') {
            array_pop($conversation);
        }

        return $conversation;
    }

    /**
     * Keyword canned answers used while (or if) the ChatbotService is absent.
     */
    private function fallbackReply(string $message): string
    {
        $text = mb_strtolower($message);

        $answers = [
            [
                'keywords' => ['hello', 'hi ', 'hey', 'good morning', 'good evening'],
                'reply' => "Hey there! I'm the Fan Hub+ assistant. Ask me about bookmarks, events, articles or how to get the most out of your dashboard.",
            ],
            [
                'keywords' => ['bookmark', 'saved', 'favourite', 'favorite'],
                'reply' => 'Hit the bookmark icon on any card to save it — everything lands on your **Bookmarks** page, where you can filter by type, add personal notes and remove items again.',
            ],
            [
                'keywords' => ['event', 'convention', 'expo', 'meetup', 'ticket'],
                'reply' => 'Check the **Events** section for conventions, screenings and meetups — you can filter by city and open any listing for dates, venue and ticket links.',
            ],
            [
                'keywords' => ['recommend', 'suggest', 'watch', 'read', 'start'],
                'reply' => 'Your **Dashboard** recommends titles from the fandoms you mark as favourites on your profile — the more you pick, the sharper the suggestions get.',
            ],
            [
                'keywords' => ['account', 'register', 'sign up', 'login', 'password', 'email'],
                'reply' => 'Create an account from the **Register** page, and use **Forgot password** on the login screen if you ever need a reset link. Verification links land in your inbox (logged in development mode).',
            ],
            [
                'keywords' => ['submit', 'submission', 'fan art', 'cosplay', 'contribute'],
                'reply' => 'Head to **Submit content** to send artwork, videos, cosplay or a write-up — moderators review every submission and you can track the status under *My submissions*.',
            ],
            [
                'keywords' => ['release', 'coming soon', 'upcoming', 'launch'],
                'reply' => 'The **Upcoming releases** list tracks anime, games, movies, comics and merchandise with dates and statuses, so you know exactly when something drops.',
            ],
        ];

        foreach ($answers as $answer) {
            foreach ($answer['keywords'] as $keyword) {
                if (Str::contains($text, $keyword)) {
                    return $answer['reply'];
                }
            }
        }

        return 'I can point you to bookmarks, events, releases, articles and your dashboard. For anything specific, drop a note on the **Feedback** page and the team will pick it up.';
    }
}
