<?php

namespace App\Http\Controllers\Api;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends ApiController
{
    /**
     * POST /api/chat — talk to the Fan Hub+ assistant.
     *
     * Body: {message, session_id?, conversation?}
     * Answers {session_id, reply, conversation}.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'session_id' => ['nullable', 'string', 'max:64'],
            'conversation' => ['nullable', 'array', 'max:40'],
            'conversation.*.role' => ['required', 'in:user,assistant'],
            'conversation.*.content' => ['required', 'string', 'max:4000'],
        ]);

        $sessionId = $data['session_id'] ?? '';

        if ($sessionId === '') {
            $sessionId = 'api-'.($request->user()?->id ?? 'guest').'-'.substr(md5($request->ip().'|'.$request->userAgent()), 0, 12);
        }

        $result = app(ChatbotService::class)->handle(
            $data['message'],
            $request->user()?->id,
            $sessionId
        );

        return response()->json([
            'session_id' => $sessionId,
            'reply' => $result['reply'],
            'conversation' => $result['conversation'],
        ]);
    }
}
