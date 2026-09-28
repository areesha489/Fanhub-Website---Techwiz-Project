<?php

namespace App\Http\Controllers\Api;

use App\Models\Feedback;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends ApiController
{
    /**
     * POST /api/feedback — send a bug report, suggestion or question.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', Rule::in(Feedback::TYPES)],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $feedback = Feedback::query()->create([
            'user_id' => $request->user()->id,
            'type' => $data['type'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => Feedback::STATUS_OPEN,
        ]);

        return response()->json(['data' => ApiSerializer::feedback($feedback)], 201);
    }
}
