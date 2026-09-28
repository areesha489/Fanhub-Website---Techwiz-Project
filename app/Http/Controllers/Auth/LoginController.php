<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Engagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Session based sign in / sign out for the website.
 *
 * GET|POST /login · POST /logout
 */
class LoginController extends Controller
{
    /**
     * Show the sign in form.
     */
    public function showLoginForm(Request $request): View
    {
        return view('auth.login', [
            'next' => self::safeNext($request),
        ]);
    }

    /**
     * Authenticate the credentials and start the session.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'email address',
            'password' => 'password',
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if ($user !== null && $user->status === User::STATUS_SUSPENDED) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This account has been suspended. Contact hello@fanhubplus.test if you believe this is a mistake.',
            ]);
        }

        if (! Auth::attempt(
            ['email' => $validated['email'], 'password' => $validated['password']],
            $request->boolean('remember')
        )) {
            throw ValidationException::withMessages([
                'email' => 'The email address or password you entered does not match our records.',
            ]);
        }

        $request->session()->regenerate();

        $authed = Auth::user();

        if ($authed instanceof User) {
            Engagement::log($authed->id, 'login', 'Signed in to Fan Hub+');
        }

        $next = self::safeNext($request);

        if ($next !== null) {
            return redirect()->to($next);
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Sign the member out and rotate the session identifiers.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user instanceof User) {
            Engagement::log($user->id, 'logout', 'Signed out of Fan Hub+');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out. See you around!');
    }

    /**
     * Only honour same-origin absolute paths as the post-login destination,
     * so the redirect can never be used for an open redirect.
     */
    private static function safeNext(Request $request): ?string
    {
        $candidate = $request->query('next');

        if (! is_string($candidate)) {
            $candidate = $request->input('next');
        }

        if (! is_string($candidate)) {
            return null;
        }

        $candidate = trim($candidate);

        if ($candidate === '' || ! Str::startsWith($candidate, '/')) {
            return null;
        }

        if (Str::startsWith($candidate, '//') || Str::contains($candidate, ['\\', "\r", "\n"])) {
            return null;
        }

        return $candidate;
    }
}
