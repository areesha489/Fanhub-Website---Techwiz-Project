<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database in dependency order.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            TagSeeder::class,
            ContentSeeder::class,
            CharacterSeeder::class,
            ArticleSeeder::class,
            MediaSeeder::class,
            EventSeeder::class,
            MerchandiseSeeder::class,
            ReleaseSeeder::class,
            ChatbotKnowledgeSeeder::class,
            EngagementSeeder::class,
            FeedbackSeeder::class,
            FanSubmissionSeeder::class,
            CatalogueImageSeeder::class,
        ]);
    }
}
