<?php

namespace Tests\Feature;

use App\Models\ChatbotKnowledge;
use App\Services\ChatbotService;
use Database\Seeders\ChatbotKnowledgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fan Hub+ assistant: knowledge-base answers without any AI provider.
 */
class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ChatbotKnowledgeSeeder::class);

        // Force the offline knowledge-base path for every test in this file.
        config(['ai.api_key' => null, 'ai.enabled' => true]);
    }

    public function test_handle_returns_a_reply_and_conversation_for_a_seeded_question(): void
    {
        $question = 'How do I create an account?';
        $knowledge = ChatbotKnowledge::query()
            ->where('question', $question)
            ->firstOrFail();

        $result = app(ChatbotService::class)->handle($question, null, 'session-create-account');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('reply', $result);
        $this->assertArrayHasKey('conversation', $result);
        $this->assertSame($knowledge->answer, $result['reply']);

        $conversation = $result['conversation'];
        $this->assertNotEmpty($conversation);
        $this->assertSame('assistant', end($conversation)['role']);
        $this->assertSame($knowledge->answer, end($conversation)['content']);

        $this->assertDatabaseHas('chatbot_queries', [
            'message' => $question,
            'session_id' => 'session-create-account',
        ]);
    }

    public function test_handle_falls_back_to_the_offline_reply_for_an_unknown_question(): void
    {
        $result = app(ChatbotService::class)->handle(
            'zzz qqqj wxyv nonsense question with no match at all',
            null,
            'session-offline'
        );

        $this->assertIsString($result['reply']);
        $this->assertNotSame('', trim($result['reply']));
        $this->assertNotEmpty($result['conversation']);
    }

    public function test_chat_endpoint_answers_guests_with_session_and_conversation(): void
    {
        $this->postJson('/chat', ['message' => 'What is Fan Hub?'])
            ->assertOk()
            ->assertJsonStructure(['session_id', 'reply', 'conversation']);

        $this->assertDatabaseCount('chatbot_queries', 1);
    }

    public function test_chat_endpoint_rejects_an_empty_message(): void
    {
        $this->postJson('/chat', [])->assertUnprocessable()->assertJsonValidationErrors('message');
    }
}
