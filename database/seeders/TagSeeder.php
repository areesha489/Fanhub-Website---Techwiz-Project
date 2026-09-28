<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Curated tags attached to catalogue titles via content_tags.
     */
    public function run(): void
    {
        $tags = [
            'Shonen', 'Seinen', 'Dark Fantasy', 'Slice of Life', 'Isekai', 'Mecha',
            'Sports', 'Retro', 'Indie', 'Speedrun', 'Esports', 'Open World',
            'Roguelike', 'Narrative', 'K-Pop', 'Comeback', 'Concert', 'Cosplay',
            'Craft', 'Manga', 'Superheroes', 'Noir', 'Heist', 'Found Family',
            'Villains', 'Animation', 'Dubbed', 'OST', 'Convention', 'Trailer',
            'Interview', 'Collectible', 'Limited Edition', 'Preorder', 'Fan Art', 'Deep Dive',
        ];

        foreach ($tags as $name) {
            Tag::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
