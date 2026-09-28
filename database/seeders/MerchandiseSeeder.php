<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Merchandise;
use Database\Seeders\Concerns\UniqueSlug;
use Illuminate\Database\Seeder;

class MerchandiseSeeder extends Seeder
{
    use UniqueSlug;

    /**
     * Eighteen showcase products across every fandom bucket — exactly three
     * upcoming, a couple without a price (archive/exhibit items), everything
     * else priced in. Fan Hub+ never sells anything; this is a gallery.
     */
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');

        $rows = [
            ['title' => "Tanjiro's Warding Mask Replica", 'cat' => 'anime', 'fandom' => 'Demon Slayer',
                'desc' => 'Hand-finished resin replica of the warding mask, strung on braided cord with a display stand included.',
                'price' => 64.99, 'currency' => 'USD', 'tags' => ['Replica', 'Limited Edition'],
                'upcoming' => false, 'date' => null, 'pop' => 88],

            ['title' => 'Zenitsu Lightning Katana Prop', 'cat' => 'anime', 'fandom' => 'Demon Slayer',
                'desc' => 'Convention-legal foam prop with a painted lightning hamon. Archive listing — the run sold out and we do not restock.',
                'price' => null, 'currency' => 'USD', 'tags' => ['Prop', 'Convention'],
                'upcoming' => false, 'date' => null, 'pop' => 81],

            ['title' => 'Frieren Staff — 1/6 Scale Prop', 'cat' => 'anime', 'fandom' => "Frieren: Beyond Journey's End",
                'desc' => 'Display-scale staff with weathered gold leaf detail and a felt-lined wall mount.',
                'price' => 149.00, 'currency' => 'USD', 'tags' => ['Prop', 'Collectible'],
                'upcoming' => false, 'date' => null, 'pop' => 79],

            ['title' => 'Elden Ring — Cloth Map of the Lands Between', 'cat' => 'gaming', 'fandom' => 'Elden Ring',
                'desc' => 'Woven cloth map with sites of grace marked in metallic thread. Folds flat, survives conventions.',
                'price' => 34.99, 'currency' => 'USD', 'tags' => ['Map', 'Collectible'],
                'upcoming' => false, 'date' => null, 'pop' => 92],

            ['title' => 'Malenia Prosthetic Arm Replica', 'cat' => 'gaming', 'fandom' => 'Elden Ring',
                'desc' => 'Wearable forearm replica in layered brass finish, sized for display or a very committed costume.',
                'price' => 219.00, 'currency' => 'USD', 'tags' => ['Replica', 'Limited Edition'],
                'upcoming' => false, 'date' => null, 'pop' => 87],

            ['title' => "Baldur's Gate 3 Dice Set — Illithid Purple", 'cat' => 'gaming', 'fandom' => "Baldur's Gate 3",
                'desc' => 'Seven resin dice with silver ink and a tadpole engraving on the d20. Cotton pouch included.',
                'price' => 24.99, 'currency' => 'USD', 'tags' => ['Dice', 'Tabletop'],
                'upcoming' => false, 'date' => null, 'pop' => 84],

            ['title' => 'Celeste Madeline Pixel Figurine', 'cat' => 'gaming', 'fandom' => 'Celeste',
                'desc' => 'Small blind-box figure series based on the sprite work, one variant glowing in the dark.',
                'price' => 42.00, 'currency' => 'USD', 'tags' => ['Figure', 'Indie'],
                'upcoming' => false, 'date' => null, 'pop' => 76],

            ['title' => 'Across the Spider-Verse Screen-Print Poster', 'cat' => 'movies', 'fandom' => 'Spider-Man: Across the Spider-Verse',
                'desc' => 'Four-colour screen print on heavy stock, numbered edition of five hundred, rolled in a tube.',
                'price' => 29.99, 'currency' => 'USD', 'tags' => ['Poster', 'Print'],
                'upcoming' => false, 'date' => null, 'pop' => 90],

            ['title' => 'Dune — Spice Trader Enamel Pin', 'cat' => 'movies', 'fandom' => 'Dune: Part Two',
                'desc' => 'Hard-enamel pin with a glitter fill for the spice bloom. Rubber clutch, butterfly backing.',
                'price' => 14.99, 'currency' => 'USD', 'tags' => ['Pin', 'Limited Edition'],
                'upcoming' => false, 'date' => null, 'pop' => 74],

            ['title' => 'Severance — Lumon Employee Handbook (Facsimile)', 'cat' => 'tv-shows', 'fandom' => 'Severance',
                'desc' => 'Aged-paper facsimile of the onboarding booklet with the full wellness poster folded inside.',
                'price' => null, 'currency' => 'USD', 'tags' => ['Book', 'Replica'],
                'upcoming' => false, 'date' => null, 'pop' => 85],

            ['title' => 'Avatar Elements Ceramic Mug Set', 'cat' => 'tv-shows', 'fandom' => 'Avatar: The Last Airbender',
                'desc' => 'Four stoneware mugs, one glaze per nation, nested in a single printed box.',
                'price' => 46.00, 'currency' => 'USD', 'tags' => ['Homewares', 'Set'],
                'upcoming' => false, 'date' => null, 'pop' => 82],

            ['title' => 'NewJeans — Official Lightstick (2026 Ver.)', 'cat' => 'k-pop', 'fandom' => 'NewJeans',
                'desc' => 'Bluetooth-synced lightstick with the refreshed colour profile and a replaceable battery cradle.',
                'price' => 72.00, 'currency' => 'USD', 'tags' => ['Concert', 'Collectible'],
                'upcoming' => true, 'date' => '2026-11-20', 'pop' => 91],

            ['title' => 'BTS — Proof Vinyl Box Set', 'cat' => 'k-pop', 'fandom' => 'BTS',
                'desc' => 'Three-LP set with a stitched booklet of liner notes and a foil-stamped slipcase.',
                'price' => 129.00, 'currency' => 'USD', 'tags' => ['Vinyl', 'Limited Edition'],
                'upcoming' => false, 'date' => null, 'pop' => 86],

            ['title' => 'One Piece — Straw Hat Wearable Replica', 'cat' => 'manga', 'fandom' => 'One Piece',
                'desc' => 'Woven straw hat with the painted red band, sized for wear and finished with a chin cord.',
                'price' => 54.00, 'currency' => 'EUR', 'tags' => ['Replica', 'Cosplay'],
                'upcoming' => false, 'date' => null, 'pop' => 93],

            ['title' => 'Chainsaw Man — Pull-Start Plush', 'cat' => 'manga', 'fandom' => 'Chainsaw Man',
                'desc' => 'Plush with a pull-cord that triggers a recorded rev sound. Batteries pre-installed.',
                'price' => 38.00, 'currency' => 'USD', 'tags' => ['Plush', 'Figure'],
                'upcoming' => false, 'date' => null, 'pop' => 80],

            ['title' => 'Court of Owls — Gotham Tarot Deck', 'cat' => 'comics', 'fandom' => 'Batman: Court of Owls',
                'desc' => 'Seventy-eight card deck reinterpreted with the arc\'s iconography, boxed with a meaning guide.',
                'price' => 27.50, 'currency' => 'GBP', 'tags' => ['Cards', 'Collectible'],
                'upcoming' => false, 'date' => null, 'pop' => 72],

            ['title' => 'Cosplay Craft Masters 2026 — Ticket + Kit Bundle', 'cat' => 'cosplay', 'fandom' => 'Fan Hub+ Events',
                'desc' => 'Weekend pass bundled with the starter kit: contact cement, sealant sample and the judge-question card.',
                'price' => null, 'currency' => 'EUR', 'tags' => ['Convention', 'Bundle'],
                'upcoming' => true, 'date' => '2027-01-10', 'pop' => 68],

            ['title' => 'Saga — Deluxe Slipcase (Anniversary Print)', 'cat' => 'comics', 'fandom' => 'Saga',
                'desc' => 'Cloth-covered slipcase holding the first three volumes with new cover art for the anniversary run.',
                'price' => 94.00, 'currency' => 'USD', 'tags' => ['Box Set', 'Print'],
                'upcoming' => true, 'date' => '2026-12-01', 'pop' => 77],
        ];

        foreach ($rows as $row) {
            Merchandise::query()->updateOrCreate(
                ['slug' => $this->uniqueSlug('merchandise', $row['title'])],
                [
                    'title' => $row['title'],
                    'category_id' => $categories[$row['cat']],
                    'fandom' => $row['fandom'],
                    'description' => $row['desc'],
                    'image' => null,
                    'price' => $row['price'],
                    'currency' => $row['currency'],
                    'tags' => $row['tags'],
                    'is_upcoming' => $row['upcoming'],
                    'release_date' => $row['date'],
                    'popularity' => $row['pop'],
                    'status' => Merchandise::STATUS_PUBLISHED,
                ]
            );
        }

        // Volume rows via A1's factory (category pinned, never upcoming so the
        // curated three remain the only flagged products).
        Merchandise::factory()->count(3)->create([
            'category_id' => $categories->random(),
            'is_upcoming' => false,
        ]);
    }
}
