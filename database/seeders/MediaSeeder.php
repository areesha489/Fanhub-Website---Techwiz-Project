<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\Concerns\UniqueSlug;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    use UniqueSlug;

    /**
     * Multimedia library: videos, trailers and audio (public YouTube ids
     * verified via oEmbed) plus galleries of generated stills.
     */
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');
        $adminId = User::query()->where('email', 'admin@fanhubplus.test')->value('id');

        $rows = [
            // ---- Trailers (4) ----
            ['title' => 'Demon Slayer: Kimetsu no Yaiba — Official Trailer', 'cat' => 'anime', 'type' => 'trailer',
                'desc' => 'The campaign trailer that sold the water-breathing fights and the family stake in ninety seconds flat.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=VQGCKyvzIM4', 'duration' => '1:41',
                'views' => 412000, 'rating' => 9.1, 'featured' => true, 'gallery' => null],

            ['title' => 'Dune: Part Two — Official Trailer', 'cat' => 'movies', 'type' => 'trailer',
                'desc' => 'Worm rides, war drums and a prophecy closing around Paul — the teaser that rebuilt the box office forecast.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=Way9Dexny3w', 'duration' => '2:40',
                'views' => 386000, 'rating' => 8.9, 'featured' => false, 'gallery' => null],

            ['title' => 'Elden Ring — Official Gameplay Reveal', 'cat' => 'gaming', 'type' => 'trailer',
                'desc' => 'The reveal that taught an entire forum to read environment art for boss placement.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=E3Huy2cdih0', 'duration' => '5:16',
                'views' => 344000, 'rating' => 9.3, 'featured' => true, 'gallery' => null],

            ['title' => 'Avatar: The Last Airbender — Official Trailer', 'cat' => 'tv-shows', 'type' => 'trailer',
                'desc' => 'Netflix official trailer for Avatar: The Last Airbender.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=ByAn8DF8Ykk', 'duration' => '2:28',
                'views' => 96000, 'rating' => 8.2, 'featured' => false, 'gallery' => null],

            ['title' => 'Celeste — Launch Trailer', 'cat' => 'gaming', 'type' => 'trailer',
                'desc' => 'Maddy Thorson\'s launch trailer: Madeline, the mountain, and the reflection that climbs with her.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=FqBj2IGg6Uw', 'duration' => '1:24',
                'views' => 74000, 'rating' => 8.8, 'featured' => false, 'gallery' => null],

            ['title' => 'Inside Out 2 — Official Trailer', 'cat' => 'movies', 'type' => 'trailer',
                'desc' => 'Pixar\'s official trailer, the one where Anxiety shows up at headquarters with luggage.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=LEjhY15eCx0', 'duration' => '2:25',
                'views' => 186000, 'rating' => 8.2, 'featured' => false, 'gallery' => null],

            ['title' => 'Everything Everywhere All at Once — Official Trailer', 'cat' => 'movies', 'type' => 'trailer',
                'desc' => 'A24\'s official trailer: a laundromat audit that turns into every life Evelyn did not live.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=wxN1T1uxQ2g', 'duration' => '2:42',
                'views' => 164000, 'rating' => 8.7, 'featured' => false, 'gallery' => null],

            ['title' => 'The Bear — Official Trailer', 'cat' => 'tv-shows', 'type' => 'trailer',
                'desc' => 'FX\'s official trailer for The Bear, the Chicago kitchen that runs on grief and ticket times.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=ojjCvICC86c', 'duration' => '2:07',
                'views' => 151000, 'rating' => 8.6, 'featured' => false, 'gallery' => null],

            ['title' => 'Fallout — Official Trailer', 'cat' => 'tv-shows', 'type' => 'trailer',
                'desc' => 'Prime Video\'s official trailer: a vault dweller walks into the wasteland the games already joked about.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=f3yQVMtx980', 'duration' => '3:17',
                'views' => 198000, 'rating' => 8.4, 'featured' => false, 'gallery' => null],

            ['title' => 'Batman: Court of Owls — Official Trailer', 'cat' => 'comics', 'type' => 'trailer',
                'desc' => 'Trailer for Batman vs. Robin, the animated film built on the Court of Owls story.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=qtdJl6ccK18', 'duration' => '1:13',
                'views' => 88000, 'rating' => 8.5, 'featured' => false, 'gallery' => null],

            ['title' => 'Watchmen — Official Trailer', 'cat' => 'comics', 'type' => 'trailer',
                'desc' => 'Warner Bros. teaser for the animated Watchmen chapters, the graphic novel put on screen.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=bdUESyHY8go', 'duration' => '0:42',
                'views' => 112000, 'rating' => 8.9, 'featured' => false, 'gallery' => null],

            ['title' => 'One Piece — Official Trailer', 'cat' => 'manga', 'type' => 'trailer',
                'desc' => 'Netflix official trailer: Luffy, the Straw Hats, and the treasure at the end of the Grand Line.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=Ades3pQbeh8', 'duration' => '3:07',
                'views' => 274000, 'rating' => 9.1, 'featured' => true, 'gallery' => null],

            ['title' => 'World Cosplay Championship — Official Trailer', 'cat' => 'cosplay', 'type' => 'trailer',
                'desc' => 'World Cosplay Summit\'s official championship trailer, the craftsmanship stage the finals coverage follows.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=QqBe8EndbLI', 'duration' => '2:05',
                'views' => 36000, 'rating' => 8.4, 'featured' => false, 'gallery' => null],

            ['title' => 'Frieren: Beyond Journey\'s End — Official Trailer', 'cat' => 'anime', 'type' => 'trailer',
                'desc' => 'The official trailer for the elf mage who outlives the party she travelled with.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=Iwr1aLEDpe4', 'duration' => '1:30',
                'views' => 210000, 'rating' => 9.0, 'featured' => false, 'gallery' => null],

            ['title' => 'Attack on Titan: The Final Season — Official Trailer', 'cat' => 'anime', 'type' => 'trailer',
                'desc' => 'The final-season trailer that drops the walls and stops pretending the story can stay small.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=MUCN-JwUvbY', 'duration' => '1:45',
                'views' => 305000, 'rating' => 9.2, 'featured' => false, 'gallery' => null],

            ['title' => 'Jujutsu Kaisen — Official Trailer', 'cat' => 'anime', 'type' => 'trailer',
                'desc' => 'The series trailer built around curses that only other curses can pay for.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=pkKu9hLT-t8', 'duration' => '1:30',
                'views' => 268000, 'rating' => 8.8, 'featured' => false, 'gallery' => null],

            ['title' => 'SPY x FAMILY — Official Trailer', 'cat' => 'anime', 'type' => 'trailer',
                'desc' => 'The Forger family trailer: a spy, an assassin and a mind reader sharing one address.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=fqWKCELEdYM', 'duration' => '1:20',
                'views' => 154000, 'rating' => 8.4, 'featured' => false, 'gallery' => null],

            ['title' => 'Hades II — Launch Trailer', 'cat' => 'gaming', 'type' => 'trailer',
                'desc' => 'Supergiant\'s launch trailer for Melinoë\'s war on Kronos.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=U8lJRcUeEMs', 'duration' => '2:10',
                'views' => 188000, 'rating' => 8.9, 'featured' => false, 'gallery' => null],

            ['title' => 'Baldur\'s Gate 3 — Launch Trailer', 'cat' => 'gaming', 'type' => 'trailer',
                'desc' => 'The launch trailer that sold the Absolute, the parasites and the party that argues the whole way.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=XuCfkgaaa08', 'duration' => '3:10',
                'views' => 276000, 'rating' => 9.4, 'featured' => true, 'gallery' => null],

            ['title' => 'Spider-Man: Across the Spider-Verse — Official Trailer', 'cat' => 'movies', 'type' => 'trailer',
                'desc' => 'Miles gets pulled across the multiverse in the second official trailer.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=shW9i6k8cB0', 'duration' => '2:30',
                'views' => 298000, 'rating' => 9.1, 'featured' => false, 'gallery' => null],

            ['title' => 'Severance — Official Trailer', 'cat' => 'tv-shows', 'type' => 'trailer',
                'desc' => 'Apple TV\'s trailer for the office that splits your memory at the elevator.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=xEQP4VVuyrY', 'duration' => '2:20',
                'views' => 241000, 'rating' => 9.0, 'featured' => false, 'gallery' => null],

            ['title' => 'Chainsaw Man: Reze Arc — Official Trailer', 'cat' => 'manga', 'type' => 'trailer',
                'desc' => 'The Reze Arc movie trailer, the big-screen cut of the manga\'s next fight.',
                'url' => null, 'embed' => 'https://www.youtube.com/watch?v=tAzAhDNdehs', 'duration' => '1:50',
                'views' => 223000, 'rating' => 8.7, 'featured' => false, 'gallery' => null],

            // ---- Videos (4) ----
            ['title' => 'Sintel — Festival Screening Cut', 'cat' => 'movies', 'type' => 'video',
                'desc' => 'The open-movie short we screen before panel discussions — a complete hero journey in fifteen minutes.',
                'url' => 'https://www.youtube.com/watch?v=eRsGyueVLvQ', 'embed' => 'https://www.youtube.com/watch?v=eRsGyueVLvQ', 'duration' => '14:48',
                'views' => 128000, 'rating' => 8.6, 'featured' => false, 'gallery' => null],

            ['title' => 'Big Buck Bunny — Animation Study Night', 'cat' => 'anime', 'type' => 'video',
                'desc' => 'Community screening pick for animators: timing, squash-and-stretch and a very committed rabbit.',
                'url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'embed' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'duration' => '09:56',
                'views' => 87000, 'rating' => 7.8, 'featured' => false, 'gallery' => null],

            ['title' => 'Tears of Steel — VFX Reference Reel', 'cat' => 'movies', 'type' => 'video',
                'desc' => 'Live action welded to CG creatures — still the clearest public demo of match-move fundamentals.',
                'url' => 'https://www.youtube.com/watch?v=R6MlUcmOul8', 'embed' => 'https://www.youtube.com/watch?v=R6MlUcmOul8', 'duration' => '12:14',
                'views' => 74000, 'rating' => 8.0, 'featured' => false, 'gallery' => null],

            ['title' => 'Foam & Fabric: Armor Basics', 'cat' => 'cosplay', 'type' => 'video',
                'desc' => 'Kamui Cosplay\'s starter lesson on EVA foam: patterns, contact cement, heat shaping and a first bracer.',
                'url' => 'https://www.youtube.com/watch?v=dN3NHy7Asqc', 'embed' => 'https://www.youtube.com/watch?v=dN3NHy7Asqc', 'duration' => '8:41',
                'views' => 24000, 'rating' => 8.0, 'featured' => false, 'gallery' => null],

            ['title' => 'Elephants Dream — Restoration Screening', 'cat' => 'anime', 'type' => 'video',
                'desc' => 'First open movie, cleaned up — surreal machine worlds that aged into a style reference.',
                'url' => 'https://www.youtube.com/watch?v=TLkA0RELQ1g', 'embed' => 'https://www.youtube.com/watch?v=TLkA0RELQ1g', 'duration' => '10:54',
                'views' => 61000, 'rating' => 7.6, 'featured' => false, 'gallery' => null],

            // ---- Audio (3) ----
            ['title' => 'Season OST Discussion — Podcast Ep. 14', 'cat' => 'anime', 'type' => 'audio',
                'desc' => 'Opening-theme listen: LiSA\'s Gurenge, the Demon Slayer cue the discussion keeps coming back to.',
                'url' => 'https://www.youtube.com/watch?v=x1FV6IrjZCY', 'embed' => 'https://www.youtube.com/watch?v=x1FV6IrjZCY', 'duration' => '3:55',
                'views' => 52000, 'rating' => 8.3, 'featured' => false, 'gallery' => null],

            ['title' => 'Soundtrack Corner: Battle Themes That Defined a Decade', 'cat' => 'gaming', 'type' => 'audio',
                'desc' => 'The Elden Ring main theme, the boss-fight cue this listen-through opens with.',
                'url' => 'https://www.youtube.com/watch?v=KBP1zYwBzOE', 'embed' => 'https://www.youtube.com/watch?v=KBP1zYwBzOE', 'duration' => '1:38',
                'views' => 47000, 'rating' => 8.7, 'featured' => true, 'gallery' => null],

            ['title' => 'Comeback Audio Teaser Archive', 'cat' => 'k-pop', 'type' => 'audio',
                'desc' => 'NewJeans official video for Super Shy, filed with the comeback audio archive.',
                'url' => 'https://www.youtube.com/watch?v=ArmDp-zijuc', 'embed' => 'https://www.youtube.com/watch?v=ArmDp-zijuc', 'duration' => '3:00',
                'views' => 68000, 'rating' => 7.9, 'featured' => false, 'gallery' => null],

            ['title' => 'NewJeans — "Get Up" (ETA)', 'cat' => 'k-pop', 'type' => 'audio',
                'desc' => 'HYBE official video for ETA, the Get Up track.',
                'url' => 'https://www.youtube.com/watch?v=jOTfBlKSQYY', 'embed' => 'https://www.youtube.com/watch?v=jOTfBlKSQYY', 'duration' => '3:37',
                'views' => 142000, 'rating' => 8.5, 'featured' => false, 'gallery' => null],

            ['title' => 'Stray Kids — "5-STAR" (S-Class)', 'cat' => 'k-pop', 'type' => 'audio',
                'desc' => 'JYP official video for S-Class, the 5-STAR title track.',
                'url' => 'https://www.youtube.com/watch?v=JsOOis4bBFg', 'embed' => 'https://www.youtube.com/watch?v=JsOOis4bBFg', 'duration' => '3:31',
                'views' => 128000, 'rating' => 8.6, 'featured' => false, 'gallery' => null],

            ['title' => 'BTS — "Proof" (Yet To Come)', 'cat' => 'k-pop', 'type' => 'audio',
                'desc' => 'HYBE official video for Yet To Come, the Proof anthology single.',
                'url' => 'https://www.youtube.com/watch?v=kXpOEzNZ8hQ', 'embed' => 'https://www.youtube.com/watch?v=kXpOEzNZ8hQ', 'duration' => '4:41',
                'views' => 176000, 'rating' => 8.4, 'featured' => false, 'gallery' => null],

            // ---- Galleries (4) ----
            ['title' => 'Anime Frontier Expo — Cosplay Floor', 'cat' => 'cosplay', 'type' => 'gallery',
                'desc' => 'Floor shots from last year\'s craftsmanship stage — armour, wigs and the queue that wrapped the hall twice.',
                'url' => null, 'embed' => null, 'duration' => null,
                'views' => 92000, 'rating' => 8.8, 'featured' => true,
                'gallery' => ['floor', 'stage walk', 'craftsmanship interview', 'group shot', 'backstage']],

            ['title' => 'Artist Alley Sketchbook Tour', 'cat' => 'comics', 'type' => 'gallery',
                'desc' => 'Tables, commissions and the original pages that never made it into print.',
                'url' => null, 'embed' => null, 'duration' => null,
                'views' => 44000, 'rating' => 8.4, 'featured' => false,
                'gallery' => ['alley wide shot', 'commission wall', 'inked page', 'artist portrait']],

            ['title' => 'Practical Effects: Miniatures & Sets', 'cat' => 'movies', 'type' => 'gallery',
                'desc' => 'Behind-the-canvas stills from miniature builds and painted backdrops used in recent productions.',
                'url' => null, 'embed' => null, 'duration' => null,
                'views' => 57000, 'rating' => 8.1, 'featured' => false,
                'gallery' => ['model city', 'blue screen pass', 'painted backdrop', 'lighting rig']],

            ['title' => 'Retro Hardware Preservation Gallery', 'cat' => 'gaming', 'type' => 'gallery',
                'desc' => 'Consoles, carts and repair benches from the archive room — everything photographed before recapping.',
                'url' => null, 'embed' => null, 'duration' => null,
                'views' => 38000, 'rating' => 8.5, 'featured' => false,
                'gallery' => ['cartridge wall', 'open console', 'CRT shelf', 'label macro']],
        ];

        foreach ($rows as $row) {
            $gallery = $row['gallery'] !== null
                ? array_map(fn (string $label): string => ph($row['title'].' — '.$label, 800, 600, $row['cat']), $row['gallery'])
                : null;

            $existing = Media::query()->where('title', $row['title'])->first();

            Media::query()->updateOrCreate(
                ['slug' => $existing?->slug ?? $this->uniqueSlug('media', $row['title'])],
                [
                    'title' => $row['title'],
                    'category_id' => $categories[$row['cat']],
                    'media_type' => $row['type'],
                    'description' => $row['desc'],
                    'thumbnail' => null,
                    'url' => $row['url'],
                    'embed_url' => $row['embed'],
                    'duration' => $row['duration'],
                    'gallery' => $gallery,
                    'views_count' => $row['views'],
                    'rating' => $row['rating'],
                    'featured' => $row['featured'],
                    'status' => Media::STATUS_PUBLISHED,
                    'created_by' => $adminId,
                ]
            );
        }
    }
}
