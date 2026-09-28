<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Support\Engagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contact / feedback form.
 *
 * GET|POST /feedback
 */
class FeedbackController extends Controller
{
    /**
     * Render the form (plus the member's own tickets when signed in).
     */
    public function create(Request $request): View
    {
        return $this->render($request, null);
    }

    /**
     * Store a ticket and show the thank-you state on the same page.
     */
    public function store(Request $request): RedirectResponse|View
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', Feedback::TYPES)],
            'subject' => ['required', 'string', 'min:5', 'max:255'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
        ], [], [
            'subject' => 'subject',
            'message' => 'message',
        ]);

        $feedback = Feedback::query()->create([
            'user_id' => $request->user()?->getKey(),
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => Feedback::STATUS_OPEN,
        ]);

        if ($request->user()) {
            Engagement::log($request->user()->getKey(), 'feedback', 'Sent feedback: '.$feedback->subject, Feedback::class, $feedback->getKey());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'id' => $feedback->id,
            ], 201);
        }

        return $this->render($request, $feedback);
    }

    /**
     * Store a newsletter address as an admin inbox ticket.
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        Feedback::query()->create([
            'user_id' => $request->user()?->getKey(),
            'type' => 'query',
            'subject' => 'Newsletter signup',
            'message' => 'Add this address to release and event updates: '.$validated['email'],
            'status' => Feedback::STATUS_OPEN,
        ]);

        return back()->with('success', 'You are on the list. Release and event notes will go to that address.');
    }

    /**
     * Build the page: form shell + ticket confirmation + own tickets.
     */
    private function render(Request $request, ?Feedback $ticket): View
    {
        $user = $request->user();

        return view('feedback.create', [
            'types' => Feedback::TYPES,
            'ticket' => $ticket,
            'myFeedback' => $user
                ? Feedback::query()
                    ->where('user_id', $user->getKey())
                    ->latest('id')
                    ->limit(10)
                    ->get()
                : collect(),
        ]);
    }
}
