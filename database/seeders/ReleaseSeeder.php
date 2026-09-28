<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Content;
use App\Models\Release;
use Database\Seeders\Concerns\UniqueSlug;
use Illuminate\Database\Seeder;

class ReleaseSeeder extends Seeder
{
    use UniqueSlug;

    /**
     * Twelve release-calendar entries: seven upcoming inside the next six
     * months, four already out, exactly one delayed — one of every type in
     * Release::TYPES.
     */
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');
        $contents = Content::query()->pluck('id', 'slug');

        $rows = [
            // ---- Upcoming (7) ----
            ['title' => 'One Piece — Elbaf Arc Part 1', 'type' => 'anime', 'cat' => 'manga', 'content' => 'one-piece',
                'date' => '2026-10-04', 'status' => 'upcoming', 'pop' => 96,
                'desc' => 'The collected edition of the giant-island chapters, with a fold-out map of the first village.'],

            ['title' => 'Hades II — Version 1.0', 'type' => 'game', 'cat' => 'gaming', 'content' => 'hades-ii',
                'date' => '2026-11-18', 'status' => 'upcoming', 'pop' => 94,
                'desc' => 'The full story update that closes early access, adding the final region and the last of the incantation tree.'],

            ['title' => 'Dune: Messiah', 'type' => 'movie', 'cat' => 'movies', 'content' => 'dune-part-two',
                'date' => '2026-12-18', 'status' => 'upcoming', 'pop' => 90,
                'desc' => 'The sequel chapter, arriving in the holiday corridor with the same creative team returning.'],

            ['title' => 'Severance — Season 3', 'type' => 'tv', 'cat' => 'tv-shows', 'content' => 'severance',
                'date' => '2027-02-12', 'status' => 'upcoming', 'pop' => 92,
                'desc' => 'Ten episodes, weekly rollout, with the premiere double-dropped on release night.'],

            ['title' => 'Chainsaw Man — Part 3, Vol. 1', 'type' => 'comic', 'cat' => 'manga', 'content' => 'chainsaw-man',
                'date' => '2026-11-05', 'status' => 'upcoming', 'pop' => 85,
                'desc' => 'First collected volume of the new arc, printed with the colour pages restored at original size.'],

            ['title' => 'Demon Slayer — Art Collection (Home Video Artbook)', 'type' => 'merchandise', 'cat' => 'anime', 'content' => 'demon-slayer-kimetsu-no-yaiba',
                'date' => '2026-10-30', 'status' => 'upcoming', 'pop' => 78,
                'desc' => 'Two hundred pages of background art and key animation frames, shipped with the steelbook reprint.'],

            ['title' => "Baldur's Gate 3 — Epilogue Pass", 'type' => 'game', 'cat' => 'gaming', 'content' => 'baldurs-gate-3',
                'date' => '2027-01-22', 'status' => 'upcoming', 'pop' => 88,
                'desc' => 'Free update adding an epilogue chapter and the long-promised Honour mode balance sweep.'],

            // ---- Delayed (exactly 1) ----
            ['title' => 'Evangelion: Rebuild — The Final Cut (Restoration)', 'type' => 'anime', 'cat' => 'anime', 'content' => 'neon-genesis-evangelion-rebuild-retrospective',
                'date' => '2027-03-19', 'status' => 'delayed', 'pop' => 80,
                'desc' => 'The 4K restoration slipped while the audio remaster finishes — revised date confirmed by the studio.'],

            // ---- Released (4) ----
            ['title' => 'Frieren — Season 2 (Blu-ray)', 'type' => 'anime', 'cat' => 'anime', 'content' => 'frieren-beyond-journeys-end',
                'date' => '2026-08-28', 'status' => 'released', 'pop' => 87,
                'desc' => 'Region-free set with the commentary tracks and a booklet of the season\'s layout sketches.'],

            ['title' => 'Elden Ring — Expansion Soundtrack Vinyl', 'type' => 'merchandise', 'cat' => 'gaming', 'content' => 'elden-ring',
                'date' => '2026-07-15', 'status' => 'released', 'pop' => 84,
                'desc' => 'Double LP pressed on smoke-coloured vinyl with the full boss-theme sequence.'],

            ['title' => 'Inside Out 2 — Streaming Premiere', 'type' => 'movie', 'cat' => 'movies', 'content' => 'inside-out-2',
                'date' => '2026-06-12', 'status' => 'released', 'pop' => 79,
                'desc' => 'The streaming window opened in June with the extras package included from day one.'],

            ['title' => 'Watchmen — Absolute Edition Reprint', 'type' => 'comic', 'cat' => 'comics', 'content' => 'watchmen',
                'date' => '2026-09-05', 'status' => 'released', 'pop' => 73,
                'desc' => 'Oversized reprint with the supplementary documents restored in the appendix.'],
        ];

        foreach ($rows as $row) {
            Release::query()->updateOrCreate(
                ['slug' => $this->uniqueSlug('releases', $row['title'])],
                [
                    'title' => $row['title'],
                    'release_type' => $row['type'],
                    'category_id' => $categories[$row['cat']],
                    'release_date' => $row['date'],
                    'description' => $row['desc'],
                    'poster' => null,
                    'status' => $row['status'],
                    'content_id' => $contents[$row['content']] ?? null,
                    'popularity' => $row['pop'],
                ]
            );
        }

        // Volume rows via A1's factory (category pinned, always upcoming so the
        // single delayed/released balance above stays exact).
        Release::factory()->count(3)->create([
            'category_id' => $categories->random(),
            'status' => Release::STATUS_UPCOMING,
        ]);
    }
}
