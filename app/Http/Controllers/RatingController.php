<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Support\BookmarkHelper;
use App\Support\Engagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * AJAX star ratings from the detail pages.
 *
 * POST /rate — open to guests, which are told to sign in first.
 */
class RatingController extends Controller
{
    /**
     * Store (or update) the 1-10 rating of the current member.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if (! $request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(BookmarkHelper::morphMap()))],
            'id' => ['required', 'integer', 'min:1'],
            'value' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $class = BookmarkHelper::morphMap()[$validated['type']];
        $modelId = (int) $validated['id'];

        if (! $class::query()->whereKey($modelId)->exists()) {
            return response()->json(['message' => 'That item no longer exists.'], 422);
        }

        $value = (int) $validated['value'];

        Rating::query()->updateOrCreate([
            'user_id' => $request->user()->getKey(),
            'rateable_type' => $class,
            'rateable_id' => $modelId,
        ], [
            'value' => $value,
        ]);

        Engagement::log(
            $request->user()->getKey(),
            'rating',
            'Rated an item '.$value.'/10',
            $class,
            $modelId
        );

        return response()->json([
            'ok' => true,
            'value' => $value,
        ]);
    }
}
