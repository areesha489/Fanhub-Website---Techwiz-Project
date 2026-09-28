<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Password reset request (step one).
 *
 * GET|POST /forgot-password — the response is intentionally identical for
 * known and unknown addresses so the form cannot be used to enumerate users.
 */
class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Queue a reset link through the password broker.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [], ['email' => 'email address']);

        Password::broker()->sendResetLink($request->only('email'));

        return back()->with('status', 'If an account matches that address, a password reset link is on its way. Check your inbox — the link expires in one hour.');
    }
}
