<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Email confirmation.
 *
 * GET /verify-email · GET /verify-email/{id}/{hash} (signed) ·
 * POST /email/verification-notification
 */
class VerificationController extends Controller
{
    /**
     * Show the "check your inbox" notice for the signed in member.
     */
    public function notice(Request $request): View
    {
        return view('auth.verify-email', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Mark the address as verified when the signed link still matches.
     */
    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::query()->find($id);

        abort_unless($user !== null, 404);

        if (! hash_equals(sha1((string) $user->getEmailForVerification()), $hash)) {
            abort(403, 'This verification link is not valid.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('dashboard')
                ->with('info', 'This email address is already verified.');
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your email address is confirmed. Thanks for verifying!');
    }

    /**
     * Re-send the confirmation mail (throttled by the route).
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 403);

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('dashboard')
                ->with('info', 'Your email address is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'A fresh verification link has been sent to '.$user->email.'.');
    }
}
