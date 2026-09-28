<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPreference;
use App\Support\Engagement;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * New member registration.
 *
 * GET|POST /register
 */
class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Create the account, send the verification notice and sign the member in.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:60'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)->letters()->numbers(),
            ],
            'terms' => ['accepted'],
        ], [], [
            'name' => 'display name',
            'terms' => 'terms of service',
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => null,
        ]);

        $user->preferences()->create(UserPreference::defaults());

        event(new Registered($user));

        try {
            $user->notify(new VerifyEmail);
        } catch (\Throwable $exception) {
            Log::warning('Verification mail failed: '.$exception->getMessage());
        }

        Auth::login($user);
        $request->session()->regenerate();

        Engagement::log($user->id, 'register', 'Created a Fan Hub+ account');

        return redirect()
            ->route('dashboard')
            ->with('success', 'Welcome to Fan Hub+, '.$user->name.'! Confirm your email address whenever you are ready.');
    }
}
