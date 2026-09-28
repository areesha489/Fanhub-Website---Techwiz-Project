<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends AdminApiController
{
    /**
     * GET /api/admin/settings — every stored setting keyed by its key,
     * plus the (redacted) assistant configuration.
     */
    public function show(): JsonResponse
    {
        return response()->json($this->data([
            'settings' => $this->settingsMap(),
            'ai' => [
                'enabled' => (bool) config('ai.enabled'),
                'provider' => config('ai.provider'),
                'model' => config('ai.model'),
                'has_api_key' => filled(config('ai.api_key')),
                'timeout' => (int) config('ai.timeout'),
            ],
        ]));
    }

    /**
     * PUT /api/admin/settings — upsert a map of key => value.
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:4000'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            if (! is_string($key) || trim($key) === '') {
                continue;
            }

            Setting::put(trim($key), $value);
        }

        return response()->json($this->data([
            'settings' => $this->settingsMap(),
            'message' => 'Settings saved.',
        ]));
    }

    /**
     * Settings as a plain key => value map.
     *
     * @return array<string, string|null>
     */
    private function settingsMap(): array
    {
        return Setting::query()
            ->orderBy('key')
            ->get()
            ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->value])
            ->all();
    }
}
