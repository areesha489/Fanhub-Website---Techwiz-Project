<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Support\Engagement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    /**
     * Feedback inbox with type/status filters and an open-count badge.
     */
    public function index(Request $request): View
    {
        $query = Feedback::query()->with('user:id,name,email');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(fn ($builder) => $builder->where('subject', 'like', $like)
                ->orWhere('message', 'like', $like));
        }

        $type = (string) $request->query('type', '');
        if (in_array($type, Feedback::TYPES, true)) {
            $query->where('type', $type);
        }

        $status = (string) $request->query('status', '');
        if (in_array($status, [Feedback::STATUS_OPEN, Feedback::STATUS_IN_REVIEW, Feedback::STATUS_RESOLVED, Feedback::STATUS_CLOSED], true)) {
            $query->where('status', $status);
        } else {
            $status = '';
        }

        $sortOptions = [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
            'subject' => 'Subject A → Z',
        ];

        $sort = (string) $request->query('sort', 'newest');
        if (! isset($sortOptions[$sort])) {
            $sort = 'newest';
        }

        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'subject' => $query->orderBy('subject', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.feedback.index', [
            'items' => $items,
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'openCount' => Feedback::query()->where('status', Feedback::STATUS_OPEN)->count(),
        ]);
    }

    /**
     * Change a feedback status and/or save the reply shown to the member.
     */
    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Feedback::STATUS_OPEN,
                Feedback::STATUS_IN_REVIEW,
                Feedback::STATUS_RESOLVED,
                Feedback::STATUS_CLOSED,
            ])],
            'admin_response' => ['nullable', 'string', 'max:5000'],
        ]);

        $feedback->status = $validated['status'];
        $feedback->admin_response = $validated['admin_response'] ?? null;
        $feedback->save();

        Engagement::log(
            $request->user()?->id,
            'profile_updated',
            'Feedback "'.$feedback->subject.'" marked '.$feedback->status,
            Feedback::class,
            $feedback->id,
        );

        if ($feedback->user_id && $validated['status'] !== Feedback::STATUS_OPEN) {
            Engagement::notify(
                (int) $feedback->user_id,
                'Your feedback was updated',
                'Your feedback "'.$feedback->subject.'" is now marked '.$feedback->status.'.',
                route('dashboard'),
            );
        }

        return redirect()
            ->route('admin.feedback.index')
            ->with('success', 'Feedback "'.$feedback->subject.'" marked '.$validated['status'].'.');
    }

    /**
     * Delete a feedback entry.
     */
    public function destroy(Feedback $feedback): RedirectResponse
    {
        $subject = $feedback->subject;
        $feedback->delete();

        return redirect()
            ->route('admin.feedback.index')
            ->with('success', 'Feedback "'.$subject.'" deleted.');
    }
}
