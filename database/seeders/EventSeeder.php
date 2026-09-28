<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use Database\Seeders\Concerns\UniqueSlug;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    use UniqueSlug;

    /**
     * Twelve events: eight cities across four continents plus one online
     * event, every one of the seven event types, and two completed in the
     * past. Ticket urls use the reserved example.com path space.
     */
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');

        $rows = [
            ['name' => 'Anime Frontier Expo 2026', 'cat' => 'anime', 'type' => 'Expo',
                'start' => '2026-11-14', 'end' => '2026-11-16', 'time' => '09:30',
                'venue' => 'Tokyo Bay Hall', 'city' => 'Tokyo', 'country' => 'Japan',
                'lat' => 35.6812, 'lng' => 139.7671, 'status' => 'upcoming', 'featured' => true, 'views' => 184000,
                'desc' => 'Three days of premieres, voice-actor panels and a showroom floor that maps the whole season in one aisle. The craftsmanship stage returns with a bigger build area this year.'],

            ['name' => 'Indie Quest Gaming Convention', 'cat' => 'gaming', 'type' => 'Convention',
                'start' => '2026-10-23', 'end' => '2026-10-25', 'time' => '10:00',
                'venue' => 'LA Convention Annex', 'city' => 'Los Angeles', 'country' => 'United States',
                'lat' => 34.0522, 'lng' => -118.2437, 'status' => 'upcoming', 'featured' => true, 'views' => 146000,
                'desc' => 'Playable demos from thirty studios, dev talks that admit their bugs, and a speedrun stage running the full weekend. Indie builds take the main hall this time.'],

            ['name' => 'Thames Comic & Fandom Fair', 'cat' => 'comics', 'type' => 'Convention',
                'start' => '2026-12-05', 'end' => '2026-12-06', 'time' => '11:00',
                'venue' => 'Riverside Expo Centre', 'city' => 'London', 'country' => 'United Kingdom',
                'lat' => 51.5074, 'lng' => -0.1278, 'status' => 'upcoming', 'featured' => false, 'views' => 87000,
                'desc' => 'Two floors of long boxes, small presses and a signing schedule dense enough to need a spreadsheet. The back-issue swap opens at eleven on Saturday.'],

            ['name' => 'Hallyu Night: Comeback Watch Party', 'cat' => 'k-pop', 'type' => 'Meetup',
                'start' => '2026-10-31', 'end' => null, 'time' => '19:00',
                'venue' => 'Mapo Fan Lounge', 'city' => 'Seoul', 'country' => 'South Korea',
                'lat' => 37.5665, 'lng' => 126.9780, 'status' => 'upcoming', 'featured' => false, 'views' => 74000,
                'desc' => 'A midnight-stage premiere watched together on the lounge\'s wall screen, with fan chants printed at the door and lightstick charging stations along the bar.'],

            ['name' => 'Cosplay Craft Masters — European Final', 'cat' => 'cosplay', 'type' => 'Workshop',
                'start' => '2027-01-16', 'end' => '2027-01-17', 'time' => '10:00',
                'venue' => 'Atelier des Arts', 'city' => 'Paris', 'country' => 'France',
                'lat' => 48.8566, 'lng' => 2.3522, 'status' => 'upcoming', 'featured' => true, 'views' => 63000,
                'desc' => 'Day one is open build sessions with mentors; day two is the craftsmanship judging that decides the European slot. Beginners can audit every table for free.'],

            ['name' => 'Berlin Pixel Retro Meetup', 'cat' => 'gaming', 'type' => 'Meetup',
                'start' => '2026-11-07', 'end' => null, 'time' => '16:00',
                'venue' => 'Kreuzberg Arcade Hall', 'city' => 'Berlin', 'country' => 'Germany',
                'lat' => 52.5200, 'lng' => 13.4050, 'status' => 'upcoming', 'featured' => false, 'views' => 41000,
                'desc' => 'CRT cabinets, a cartridge swap table and a high-score board that resets at closing. Bring your own controller; the hall supplies the quarters.'],

            ['name' => 'Manga Marathon Reading Night', 'cat' => 'manga', 'type' => 'Screening',
                'start' => '2026-12-12', 'end' => null, 'time' => '18:30',
                'venue' => 'Brooklyn Story House', 'city' => 'New York', 'country' => 'United States',
                'lat' => 40.7128, 'lng' => -74.0060, 'status' => 'upcoming', 'featured' => false, 'views' => 36000,
                'desc' => 'Adaptation blocks screen all evening with discussion pauses between arcs, plus a shelf of the source volumes to compare afterwards.'],

            ['name' => 'Global Speedrun Championship 2026', 'cat' => 'gaming', 'type' => 'Online',
                'start' => '2026-11-21', 'end' => '2026-11-22', 'time' => '12:00',
                'venue' => 'Streamed from the championship studio', 'city' => 'Online', 'country' => 'Global',
                'lat' => null, 'lng' => null, 'status' => 'upcoming', 'featured' => true, 'views' => 129000,
                'desc' => 'Forty-eight hours of category races with live splits, commentary tracks and a donation counter that funds the hosting bills.'],

            ['name' => 'Voice Actor Panel & Dub Workshop', 'cat' => 'anime', 'type' => 'Workshop',
                'start' => '2027-02-06', 'end' => null, 'time' => '13:00',
                'venue' => 'Bandra Fan Studio', 'city' => 'Mumbai', 'country' => 'India',
                'lat' => 19.0760, 'lng' => 72.8777, 'status' => 'upcoming', 'featured' => false, 'views' => 29000,
                'desc' => 'Cast members walk through ADR timing on the studio floor, then take auditions from the room. Mic technique handouts are included in the pass.'],

            ['name' => 'Bracket Rush: Local Tournament Ladder', 'slug' => 'fight-night-local-tournament-ladder', 'cat' => 'gaming', 'type' => 'Tournament',
                'start' => '2026-10-17', 'end' => null, 'time' => '17:00',
                'venue' => 'LA Convention Annex', 'city' => 'Los Angeles', 'country' => 'United States',
                'lat' => 34.0522, 'lng' => -118.2437, 'status' => 'upcoming', 'featured' => false, 'views' => 52000,
                'desc' => 'Double-elimination brackets across three games, with a casual setup row for anyone waiting on a match. Sign-ups close when the sixth bracket fills.'],

            ['name' => 'Summer Cosplay Picnic 2026', 'cat' => 'cosplay', 'type' => 'Meetup',
                'start' => '2026-07-18', 'end' => null, 'time' => '12:00',
                'venue' => 'Jardin du Rives', 'city' => 'Paris', 'country' => 'France',
                'lat' => 48.8566, 'lng' => 2.3522, 'status' => 'completed', 'featured' => false, 'views' => 24000,
                'desc' => 'Shade, portable steamer and a photo circle that survived the heat. The gallery from this meetup feeds the convention floor feature.'],

            ['name' => 'Retro Gaming Swap Meet', 'cat' => 'gaming', 'type' => 'Meetup',
                'start' => '2026-06-13', 'end' => null, 'time' => '11:00',
                'venue' => 'Kreuzberg Arcade Hall', 'city' => 'Berlin', 'country' => 'Germany',
                'lat' => 52.5200, 'lng' => 13.4050, 'status' => 'completed', 'featured' => false, 'views' => 18000,
                'desc' => 'Tables of boxed hardware, a repair bench running all afternoon and a queue for the memory card writer. Completed — the recap thread has the price guide.'],
        ];

        foreach ($rows as $row) {
            $slug = $row['slug'] ?? $this->uniqueSlug('events', $row['name']);

            Event::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $row['name'],
                    'category_id' => $categories[$row['cat']],
                    'event_type' => $row['type'],
                    'description' => $row['desc'],
                    'cover_image' => null,
                    'start_date' => $row['start'],
                    'end_date' => $row['end'],
                    'time' => $row['time'],
                    'venue' => $row['venue'],
                    'city' => $row['city'],
                    'country' => $row['country'],
                    'latitude' => $row['lat'],
                    'longitude' => $row['lng'],
                    'ticket_url' => 'https://www.example.com/tickets/'.$slug,
                    'status' => $row['status'],
                    'featured' => $row['featured'],
                    'views_count' => $row['views'],
                ]
            );
        }

        // Volume rows via A1's factory (category pinned so no stray categories appear).
        Event::factory()->count(3)->create([
            'category_id' => $categories->random(),
            'views_count' => fake()->numberBetween(5000, 300000),
        ]);
    }
}
