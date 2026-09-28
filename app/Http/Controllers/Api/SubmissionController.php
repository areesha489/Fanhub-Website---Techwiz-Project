<?php

namespace App\Http\Controllers\Api;

use App\Models\FanSubmission;
use App\Support\ApiSerializer;
use App\Support\Engagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubmissionController extends ApiController
{
    /**
     * GET /api/fan-submissions — the member's own submissions.
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $request->user()
            ->submissions()
            ->with(['category'])
            ->latest('id')
            ->paginate($this->perPage($request, 10))
            ->withQueryString();

        return response()->json(
            ApiSerializer::page($paginator, fn (FanSubmission $submission): array => ApiSerializer::submission($submission))
        );
    }

    /**
     * POST /api/fan-submissions — queue a fan work for moderation.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(FanSubmission::TYPES)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:5000'],
            'url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = 'storage/'.$request->file('image')->store('submissions', 'public');
        }

        $data['user_id'] = $request->user()->id;
        $data['status'] = FanSubmission::STATUS_PENDING;

        $submission = FanSubmission::query()->create($data);

        Engagement::log(
            $request->user()->id,
            'submission',
            'Submitted "'.$submission->title.'" for review.',
            FanSubmission::class,
            $submission->id
        );

        return response()->json(['data' => ApiSerializer::submission($submission)], 201);
    }
}
