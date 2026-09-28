<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Documented setting keys managed by this screen. Fixed on purpose —
     * admins cannot invent arbitrary keys.
     *
     * @var list<string>
     */
    private const KEYS = [
        'site_tagline',
        'announcement_bar',
        'announcement_bar_enabled',
        'chatbot_enabled',
        'contact_email',
        'posts_per_page',
    ];

    /**
     * Default value per key when nothing is stored yet.
     *
     * @return array<string, string>
     */
    private function defaults(): array
    {
        return [
            'site_tagline' => 'Explore. Discover. Connect.',
            'announcement_bar' => '',
            'announcement_bar_enabled' => '0',
            'chatbot_enabled' => '1',
            'contact_email' => 'hello@fanhubplus.test',
            'posts_per_page' => '12',
        ];
    }

    /**
     * Show the settings form, prefilled from the settings table.
     */
    public function show(): View
    {
        $defaults = $this->defaults();
        $values = [];

        foreach (self::KEYS as $key) {
            $values[$key] = Setting::value($key, $defaults[$key]);
        }

        return view('admin.settings.form', [
            'values' => $values,
            'keys' => self::KEYS,
        ]);
    }

    /**
     * Upsert the fixed key set.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_tagline' => ['required', 'string', 'max:190'],
            'announcement_bar' => ['nullable', 'string', 'max:500'],
            'announcement_bar_enabled' => ['nullable', 'in:0,1'],
            'chatbot_enabled' => ['nullable', 'in:0,1'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'posts_per_page' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $payload = [
            'site_tagline' => (string) $validated['site_tagline'],
            'announcement_bar' => (string) ($validated['announcement_bar'] ?? ''),
            'announcement_bar_enabled' => $request->boolean('announcement_bar_enabled') ? '1' : '0',
            'chatbot_enabled' => $request->boolean('chatbot_enabled') ? '1' : '0',
            'contact_email' => (string) ($validated['contact_email'] ?? ''),
            'posts_per_page' => (string) (int) $validated['posts_per_page'],
        ];

        foreach ($payload as $key => $value) {
            Setting::put($key, $value);
        }

        return redirect()
            ->route('admin.settings.show')
            ->with('success', 'Settings saved.');
    }
}
