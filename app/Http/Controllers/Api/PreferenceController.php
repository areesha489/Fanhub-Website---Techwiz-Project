<?php

namespace App\Http\Controllers\Api;

use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends ApiController
{
    /**
     * GET /api/preferences — stored preferences (defaults when unset).
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->data($this->preferencePayload($request->user()->resolvePreferences())));
    }

    /**
     * PUT /api/preferences — upsert the member's preference row.
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'theme' => ['sometimes', 'string', 'in:dark,light'],
            'font_scale' => ['sometimes', 'numeric', 'min:0.75', 'max:1.5'],
            'reduced_motion' => ['sometimes', 'boolean'],
            'email_notifications' => ['sometimes', 'boolean'],
            'content_notifications' => ['sometimes', 'boolean'],
            'event_notifications' => ['sometimes', 'boolean'],
            'newsletter' => ['sometimes', 'boolean'],
        ]);

        $preferences = UserPreference::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return response()->json($this->data($this->preferencePayload($preferences)));
    }
}
