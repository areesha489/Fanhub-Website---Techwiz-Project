<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FanSubmission;
use App\Support\Engagement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    /**
     * Moderation queue with status filter and search.
     */
    public function index(Request $request): View
    {
        $query = FanSubmission::query()->with(['user:id,name,email', 'category:id,name', 'reviewer:id,name']);

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(fn ($builder) => $builder->where('title', 'like', $like)
                ->orWhere('description', 'like', $like));
        }

        $status = (string) $request->query('status', '');
        if (in_array($status, [FanSubmission::STATUS_PENDING, FanSubmission::STATUS_APPROVED, FanSubmission::STATUS_REJECTED], true)) {
            $query->where('status', $status);
        } else {
            $status = '';
        }

        $sortOptions = [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
            'title' => 'Title A → Z',
        ];

        $sort = (string) $request->query('sort', 'newest');
        if (! isset($sortOptions[$sort])) {
            $sort = 'newest';
        }

        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'title' => $query->orderBy('title', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.submissions.index', [
            'items' => $items,
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'pendingCount' => FanSubmission::query()->where('status', FanSubmission::STATUS_PENDING)->count(),
        ]);
    }

    /**
     * Approve or reject a submission (optionally leaving an admin note).
     */
    public function status(Request $request, FanSubmission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([FanSubmission::STATUS_PENDING, FanSubmission::STATUS_APPROVED, FanSubmission::STATUS_REJECTED])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $previous = $submission->status;

        $submission->status = $validated['status'];
        $submission->admin_note = $validated['admin_note'] ?? null;

        if ($submission->status === FanSubmission::STATUS_PENDING) {
            $submission->reviewed_by = null;
            $submission->reviewed_at = null;
        } else {
            $submission->reviewed_by = $request->user()?->id;
            $submission->reviewed_at = now();
        }

        $submission->save();

        Engagement::log(
            $request->user()?->id,
            'submission',
            'Submission "'.$submission->title.'" marked '.$submission->status,
            FanSubmission::class,
            $submission->id,
        );

        if (
            $submission->user_id
            && $previous !== $submission->status
            && in_array($submission->status, [FanSubmission::STATUS_APPROVED, FanSubmission::STATUS_REJECTED], true)
        ) {
            Engagement::notify(
                (int) $submission->user_id,
                $submission->status === FanSubmission::STATUS_APPROVED ? 'Submission approved' : 'Submission reviewed',
                $submission->status === FanSubmission::STATUS_APPROVED
                    ? 'Your submission "'.$submission->title.'" was approved and is now live on Fan Hub+.'
                    : 'Your submission "'.$submission->title.'" was reviewed. Open My submissions for the note.',
                $submission->status === FanSubmission::STATUS_APPROVED && filled($submission->slug)
                    ? route('community.show', $submission->slug)
                    : route('submissions.index'),
            );
        }

        return redirect()
            ->route('admin.submissions.index')
            ->with('success', $submission->status === FanSubmission::STATUS_APPROVED
                ? 'Submission "'.$submission->title.'" is approved and now live on the site.'
                : 'Submission "'.$submission->title.'" marked '.$submission->status.'.');
    }

    /**
     * Delete a submission and its uploaded image.
     */
    public function destroy(FanSubmission $submission): RedirectResponse
    {
        $title = $submission->title;

        if ($submission->image) {
            Storage::disk('public')->delete($submission->image);
        }

        $submission->delete();

        Engagement::log(auth()->id(), 'submission', 'Deleted submission "'.$title.'"', FanSubmission::class, $submission->id);

        return redirect()
            ->route('admin.submissions.index')
            ->with('success', 'Submission "'.$title.'" deleted.');
    }
}
