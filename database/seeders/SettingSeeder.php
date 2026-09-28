<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Platform-wide settings used by the layout, chat widget and admin panel.
     */
    public function run(): void
    {
        $settings = [
            'site_name' => 'FAN HUB+',
            'site_tagline' => 'Explore. Discover. Connect.',
            'announcement_bar' => '1',
            'announcement_bar_text' => 'Fan Meetup Weekend passes open this Friday — set a reminder in Events.',
            'chatbot_enabled' => '1',
            'contact_email' => 'hello@fanhubplus.test',
            'posts_per_page' => '12',
            'site.name' => 'FAN HUB+',
            'announcement' => 'Fan Meetup Weekend passes open this Friday — set a reminder in Events.',
            'maintenance.mode' => '0',
            'submissions.open' => '1',
            'footer.copyright' => '© 2026 Fan Hub+. All rights reserved.',
        ];

        foreach ($settings as $key => $value) {
            Setting::put($key, $value);
        }
    }
}
