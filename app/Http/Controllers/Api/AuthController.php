<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserPreference;
use App\Support\ApiSerializer;
use App\Support\Engagement;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends ApiController
{
    /**
     * POST /api/login — validate credentials and issue a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid login credentials.'], 401);
        }

        if (! $user->isActive()) {
            return response()->json(['message' => 'Your account has been suspended. Contact support to appeal.'], 403);
        }

        $token = $user->createToken('api')->plainTextToken;

        Engagement::log($user->id, 'login', 'Signed in through the API.');

        return response()->json([
            'token' => $token,
            'user' => ApiSerializer::user($user),
        ]);
    }

    /**
     * POST /api/register — create an account, preferences and an API token.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ]);

        UserPreference::query()->create(
            array_merge(UserPreference::defaults(), ['user_id' => $user->id])
        );

        try {
            $user->notify(new VerifyEmail);
        } catch (\Throwable $exception) {
            report($exception);
        }

        $token = $user->createToken('api')->plainTextToken;

        Engagement::log($user->id, 'login', 'Registered a new account.');

        return response()->json([
            'token' => $token,
            'user' => ApiSerializer::user($user),
        ], 201);
    }

    /**
     * POST /api/logout — revoke the token used for the request.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Signed out.']);
    }

    /**
     * GET /api/user — the authenticated member's profile.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($this->data(ApiSerializer::user($request->user())));
    }

    /**
     * POST /api/forgot-password — always answers 200 to avoid account leaks.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'If that email address exists in our system, a password reset link has been sent.',
        ]);
    }

    /**
     * POST /api/reset-password — consume a broker reset token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => Hash::make($password)])->save();

            $user->tokens()->delete();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [trans('passwords.'.$status)],
            ]);
        }

        return response()->json(['message' => 'Password has been reset. You can sign in now.']);
    }
}
