<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Known configuration keys used by the admin settings screen.
     *
     * @var list<string>
     */
    protected const KEYS = [
        'site.name',
        'site.tagline',
        'announcement',
        'chatbot.enabled',
        'maintenance.mode',
        'footer.copyright',
        'submissions.open',
    ];

    /**
     * Values matching the known configuration keys.
     *
     * @var array<string, string>
     */
    protected const VALUES = [
        'site.name' => 'FAN HUB+',
        'site.tagline' => 'Explore. Discover. Connect.',
        'announcement' => 'Community tournament sign-ups are open until the end of the month.',
        'chatbot.enabled' => '1',
        'maintenance.mode' => '0',
        'footer.copyright' => '© 2026 Fan Hub+. All rights reserved.',
        'submissions.open' => '1',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $keyCount = count(self::KEYS);
        $index = fake()->unique()->numberBetween(0, ($keyCount * 40) - 1);
        $key = self::KEYS[$index % $keyCount];
        $volume = intdiv($index, $keyCount);

        if ($volume > 0) {
            $key .= '.'.$volume;
        }

        $value = self::VALUES[$key] ?? fake()->randomElement([
            'Enabled',
            'Disabled',
            'Season 2026',
            'Community choice',
            'Explore. Discover. Connect.',
        ]);

        return [
            'key' => $key,
            'value' => $value,
        ];
    }
}
