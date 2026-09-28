<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * The eight fandom buckets that drive the sidebar and every catalogue page.
     *
     * Icon note: the installed bootstrap-icons build ships `bi-mask`
     * (singular), so Cosplay uses that instead of the unavailable `bi-masks`.
     */
    public function run(): void
    {
        $categories = [
            ['Anime', 'bi-fire', '#8B5CF6', 'Seasonal shonen, quiet slice-of-life gems and everything between — studios, arcs and opening themes all live here.', 1],
            ['Gaming', 'bi-joystick', '#EC4899', 'Indie darlings, blockbuster releases and the speedrun boards that keep every launch competitive.', 2],
            ['Movies', 'bi-film', '#22D3EE', 'Blockbusters, festival favourites and single frames that fan artists keep redrawing for years.', 3],
            ['TV Shows', 'bi-tv', '#3B82F6', 'Prestige dramas, comfort rewatches and finale debates that somehow never actually end.', 4],
            ['K-Pop', 'bi-music-note-beamed', '#F472B6', 'Comebacks, lightstick culture and fan projects that turn a two-week promotion into an event.', 5],
            ['Comics', 'bi-book', '#FBBF24', 'Capes, indies and long-running monthly pulls, from single issues to trade paperback discussions.', 6],
            ['Manga', 'bi-book-half', '#34D399', 'Weekly chapters, seinen epics and panels that quietly changed how a page tells a story.', 7],
            ['Cosplay', 'bi-mask', '#FB7185', 'Craft, wigs and Worbla — builds, contest stages and the people behind the helmets.', 8],
        ];

        foreach ($categories as [$name, $icon, $accent, $description, $position]) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'icon' => $icon,
                    'accent' => $accent,
                    'description' => $description,
                    'position' => $position,
                    'image' => null,
                ]
            );
        }
    }
}
