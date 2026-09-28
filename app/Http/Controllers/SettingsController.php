<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use App\Support\Engagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * Account settings: profile summary, preferences, accessibility, security,
 * notification choices. PUT /settings also serves the quick toggles in the
 * topbar/sidebar that assets/js/app.js persists as JSON.
 *
 * GET /settings · PUT /settings · PUT /settings/security
 */
class SettingsController extends Controller
{
    /**
     * Tabs rendered by the settings page.
     *
     * @var list<string>
     */
    private const TABS = ['profile', 'preferences', 'accessibility', 'security', 'notifications'];

    /**
     * Boolean preference columns.
     *
     * @var list<string>
     */
    private const BOOLEAN_PREFERENCES = [
        'reduced_motion',
        'email_notifications',
        'content_notifications',
        'event_notifications',
        'newsletter',
    ];

    /**
     * Show the tabbed settings page.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $tab = (string) $request->query('tab', 'profile');

        if (! in_array($tab, self::TABS, true)) {
            $tab = 'profile';
        }

        return view('settings', [
            'user' => $user,
            'preferences' => $user->resolvePreferences(),
            'tab' => $tab,
            'tabs' => self::TABS,
        ]);
    }

    /**
     * Save profile fields, display preferences and notification choices.
     * Accepts partial payloads (the JSON toggles only send one key).
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'scope' => ['nullable', 'string', Rule::in(['profile', 'preferences', 'accessibility', 'notifications'])],
            'name' => ['nullable', 'string', 'min:3', 'max:60'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'bio' => ['nullable', 'string', 'max:500'],
            'theme' => ['nullable', 'string', Rule::in(['dark', 'light'])],
            'font_scale' => ['nullable', 'numeric', Rule::in(['0.9', '1', '1.1', '1.25'])],
            'reduced_motion' => ['nullable', 'boolean'],
            'email_notifications' => ['nullable', 'boolean'],
            'content_notifications' => ['nullable', 'boolean'],
            'event_notifications' => ['nullable', 'boolean'],
            'newsletter' => ['nullable', 'boolean'],
        ]);

        $saved = [];

        if ($request->filled('name') && $request->filled('email')) {
            $emailChanged = ! hash_equals((string) $user->email, (string) $validated['email']);

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->bio = ($validated['bio'] ?? '') !== '' ? $validated['bio'] : null;

            if ($emailChanged) {
                $user->email_verified_at = null;
            }

            $user->save();

            Engagement::log($user->getKey(), 'profile_updated', 'Updated profile details from settings');

            if ($emailChanged) {
                try {
                    $user->sendEmailVerificationNotification();
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }

            $saved['profile'] = ['name' => $user->name, 'email' => $user->email];
        }

        $preferences = [];

        foreach (['theme', 'font_scale'] as $key) {
            if ($request->exists($key)) {
                $preferences[$key] = $key === 'font_scale'
                    ? (float) $validated[$key]
                    : $validated[$key];
            }
        }

        foreach (self::BOOLEAN_PREFERENCES as $key) {
            if ($request->exists($key)) {
                $preferences[$key] = $request->boolean($key);
            }
        }

        if ($preferences !== []) {
            UserPreference::query()->updateOrCreate(
                ['user_id' => $user->getKey()],
                $preferences,
            );

            $user->unsetRelation('preferences');
            $saved['preferences'] = $preferences;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'data' => $saved,
            ]);
        }

        $scope = (string) ($validated['scope'] ?? 'preferences');
        $anchor = $scope === 'profile' ? 'profile' : ($scope === 'notifications' ? 'notifications' : $scope);

        return redirect()
            ->route('settings.show', ['tab' => $anchor])
            ->with('success', 'Settings saved.');
    }

    /**
     * Change the account password (the current one is required).
     */
    public function security(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $user = $request->user();
        $user->password = Hash::make((string) $request->input('password'));
        $user->save();

        Engagement::log($user->getKey(), 'profile_updated', 'Changed the account password');

        return redirect()
            ->route('settings.show', ['tab' => 'security'])
            ->with('success', 'Your password has been updated.');
    }
}
