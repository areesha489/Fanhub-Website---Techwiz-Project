<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\FanSubmission;
use App\Support\ApiSerializer;
use App\Support\Engagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubmissionController extends AdminApiController
{
    /**
     * GET /api/admin/submissions — moderation queue.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FanSubmission::query()->with(['user', 'category', 'reviewer']);

        $this->applySearch($request, $query, ['title', 'description']);
        $this->applyStatus($request, $query);

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $query->orderByDesc('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();

        return response()->json(
            ApiSerializer::page($paginator, fn (FanSubmission $submission): array => ApiSerializer::submission($submission))
        );
    }

    /**
     * PATCH /api/admin/submissions/{submission} — review a submission.
     */
    public function status(Request $request, FanSubmission $submission): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in([
                FanSubmission::STATUS_PENDING,
                FanSubmission::STATUS_APPROVED,
                FanSubmission::STATUS_REJECTED,
            ])],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'status' => $data['status'],
            'admin_note' => $data['admin_note'] ?? $submission->admin_note,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($submission->user_id !== null && $data['status'] !== FanSubmission::STATUS_PENDING) {
            Engagement::notify(
                $submission->user_id,
                'Submission '.$data['status'],
                $data['status'] === FanSubmission::STATUS_APPROVED
                    ? 'Your submission "'.$submission->title.'" was approved and is now live on Fan Hub+.'
                    : 'Your submission "'.$submission->title.'" has been '.$data['status'].'.',
                $data['status'] === FanSubmission::STATUS_APPROVED && filled($submission->slug)
                    ? route('community.show', $submission->slug)
                    : route('submissions.index')
            );
        }

        return response()->json([
            'data' => ApiSerializer::submission($submission->fresh(['user', 'category'])),
        ]);
    }

    /**
     * DELETE /api/admin/submissions/{submission} — remove from the queue.
     */
    public function destroy(FanSubmission $submission): JsonResponse
    {
        $submission->delete();

        return response()->json(['message' => 'Submission deleted.']);
    }
}
