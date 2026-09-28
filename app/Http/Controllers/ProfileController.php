<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Favorite;
use App\Support\Engagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Member profile, avatar and favourite fandoms.
 *
 * GET|PUT /profile · POST /profile/avatar · PUT /profile/favorites
 */
class ProfileController extends Controller
{
    /**
     * Allowed avatar uploads (extension guess + MIME + pixel check below).
     *
     * @var list<string>
     */
    private const AVATAR_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Show the profile editor.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(),
            'favoriteIds' => Favorite::query()
                ->where('user_id', $user->getKey())
                ->where('favoritable_type', Category::class)
                ->pluck('favoritable_id')
                ->map(fn ($id): int => (int) $id)
                ->all(),
            'preferences' => $user->resolvePreferences(),
        ]);
    }

    /**
     * Update the display name, email address and bio.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:60'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'bio' => ['nullable', 'string', 'max:500'],
        ], [], [
            'name' => 'display name',
            'email' => 'email address',
            'bio' => 'bio',
        ]);

        $emailChanged = ! hash_equals((string) $user->email, (string) $validated['email']);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->bio = $validated['bio'] !== null && $validated['bio'] !== ''
            ? $validated['bio']
            : null;

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        Engagement::log($user->getKey(), 'profile_updated', 'Updated profile details');

        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $exception) {
                report($exception);
            }

            return redirect()
                ->route('profile.show')
                ->with('success', 'Profile saved. We sent a new confirmation link to '.$user->email.' — verify it to keep everything unlocked.');
        }

        return redirect()->route('profile.show')->with('success', 'Your profile has been saved.');
    }

    /**
     * Replace the avatar image (jpg/jpeg/png/webp, max 2 MB).
     */
    public function avatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], ['avatar' => 'profile picture']);

        $file = $request->file('avatar');

        if ($file === null || ! $file->isValid()) {
            return back()->with('error', 'The upload did not complete. Please try again.');
        }

        $extension = strtolower((string) $file->extension());

        if (! in_array($extension, self::AVATAR_EXTENSIONS, true)) {
            return back()->withErrors(['avatar' => 'Use a JPG, PNG or WebP image.']);
        }

        $pixels = @getimagesize($file->getRealPath());

        if ($pixels === false) {
            return back()->withErrors(['avatar' => 'That file does not look like a readable image.']);
        }

        $user = $request->user();
        $previous = $user->avatar;

        $filename = Str::uuid()->toString().'.'.$extension;
        $stored = $file->storeAs('avatars', $filename, 'public');

        if ($stored === false) {
            return back()->with('error', 'We could not store your avatar. Please try again.');
        }

        $user->avatar = 'avatars/'.$filename;
        $user->save();

        if ($previous !== null && $previous !== '') {
            try {
                Storage::disk('public')->delete($previous);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        Engagement::log($user->getKey(), 'profile_updated', 'Updated the profile picture');

        return redirect()->route('profile.show')->with('success', 'Profile picture updated.');
    }

    /**
     * Sync the favourite fandoms (categories) of the member.
     */
    public function favorites(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categories' => ['array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ]);

        $user = $request->user();
        $selected = collect($validated['categories'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $existing = Favorite::query()
            ->where('user_id', $user->getKey())
            ->where('favoritable_type', Category::class)
            ->get();

        foreach ($existing as $favorite) {
            if (! $selected->contains((int) $favorite->favoritable_id)) {
                $favorite->delete();
            }
        }

        $already = $existing
            ->pluck('favoritable_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        foreach ($selected as $categoryId) {
            if (in_array($categoryId, $already, true)) {
                continue;
            }

            Favorite::query()->create([
                'user_id' => $user->getKey(),
                'favoritable_type' => Category::class,
                'favoritable_id' => $categoryId,
            ]);
        }

        Engagement::log($user->getKey(), 'profile_updated', 'Updated favourite fandoms');

        return redirect()
            ->route('profile.show')
            ->with('success', 'Your favourite fandoms have been updated.');
    }
}
