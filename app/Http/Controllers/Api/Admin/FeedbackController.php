<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Feedback;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends AdminApiController
{
    /**
     * GET /api/admin/feedback — feedback inbox.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Feedback::query()->with('user');

        $this->applySearch($request, $query, ['subject', 'message']);
        $this->applyStatus($request, $query);

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $query->orderByDesc('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();

        return response()->json(
            ApiSerializer::page($paginator, fn (Feedback $feedback): array => ApiSerializer::feedback($feedback))
        );
    }

    /**
     * PATCH /api/admin/feedback/{feedback} — update status and/or reply.
     */
    public function update(Request $request, Feedback $feedback): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'string', Rule::in([
                Feedback::STATUS_OPEN,
                Feedback::STATUS_IN_REVIEW,
                Feedback::STATUS_RESOLVED,
                Feedback::STATUS_CLOSED,
            ])],
            'admin_response' => ['nullable', 'string', 'max:5000'],
        ]);

        $feedback->update($data);

        return response()->json(['data' => ApiSerializer::feedback($feedback->fresh('user'))]);
    }

    /**
     * DELETE /api/admin/feedback/{feedback} — remove a message.
     */
    public function destroy(Feedback $feedback): JsonResponse
    {
        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted.']);
    }
}
