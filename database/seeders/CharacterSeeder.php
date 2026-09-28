<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Character;
use App\Models\CharacterRelation;
use App\Models\Content;
use App\Models\ContentCharacter;
use Database\Seeders\Concerns\UniqueSlug;
use Illuminate\Database\Seeder;

class CharacterSeeder extends Seeder
{
    use UniqueSlug;

    /**
     * Twenty curated characters linked to their titles, with four+
     * declared relationships between them.
     */
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');
        $contents = Content::query()->pluck('id', 'slug');

        $rows = [
            ['name' => 'Tanjiro Kamado', 'cat' => 'anime', 'fandom' => 'Demon Slayer: Kimetsu no Yaiba', 'content' => 'demon-slayer-kimetsu-no-yaiba', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => '13-16', 'affiliation' => 'Demon Slayer Corps',
                'abilities' => 'Water Breathing, Sun Breathing, Tracking sense of smell', 'debut' => 'Chapter 1',
                'views' => 412000, 'pop' => 97, 'featured' => true,
                'gallery' => true,
                'bio' => 'A charcoal seller who keeps a journal of every person the demons took. His nose tracks demons better than any map, and his swords work because he refuses to stop breathing calmly.'],

            ['name' => 'Nezuko Kamado', 'cat' => 'anime', 'fandom' => 'Demon Slayer: Kimetsu no Yaiba', 'content' => 'demon-slayer-kimetsu-no-yaiba', 'role' => 'Supporting',
                'gender' => 'Female', 'age' => '12 (appears 14)', 'affiliation' => 'Demon Slayer Corps',
                'abilities' => 'Blood Demon Art: Exploding Blood, Bamboo restraint', 'debut' => 'Chapter 1',
                'views' => 356000, 'pop' => 93, 'featured' => false, 'gallery' => false,
                'bio' => 'Turned into a demon the same night her family fell, she kept her kindness and lost her appetite for people. Her size-shifting growth powers have bailed the Corps out of worse situations than pride admits.'],

            ['name' => 'Frieren', 'cat' => 'anime', 'fandom' => "Frieren: Beyond Journey's End", 'content' => 'frieren-beyond-journeys-end', 'role' => 'Protagonist',
                'gender' => 'Female', 'age' => 'Over 1,000', 'affiliation' => 'Hero Party of the Emblem',
                'abilities' => 'Elven magic, Zoltraak, Mana suppression', 'debut' => 'Chapter 1',
                'views' => 298000, 'pop' => 94, 'featured' => true, 'gallery' => false,
                'bio' => 'An elf mage who measured her adventures in decades and her friendships in afterthoughts. Her quietest spells are the dangerous ones because she spent centuries pretending she was average.'],

            ['name' => 'Satoru Gojo', 'cat' => 'anime', 'fandom' => 'Jujutsu Kaisen', 'content' => 'jujutsu-kaisen', 'role' => 'Supporting',
                'gender' => 'Male', 'age' => '28', 'affiliation' => 'Tokyo Jujutsu High',
                'abilities' => 'Infinity, Six Eyes, Domain Expansion: Unlimited Void', 'debut' => 'Chapter 2',
                'views' => 468000, 'pop' => 98, 'featured' => true,
                'gallery' => true,
                'bio' => 'The strongest sorcerer alive and a teacher who shows up late on purpose. He keeps his blindfold on because seeing everything at once is exhausting when you also have to grade papers.'],

            ['name' => 'Yuji Itadori', 'cat' => 'anime', 'fandom' => 'Jujutsu Kaisen', 'content' => 'jujutsu-kaisen', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => '15', 'affiliation' => 'Tokyo Jujutsu High',
                'abilities' => 'Divergent Fist, Black Flash, Sukuna vessel', 'debut' => 'Chapter 1',
                'views' => 389000, 'pop' => 95, 'featured' => false, 'gallery' => false,
                'bio' => 'A track star who swallowed a cursed finger so his friends would not have to. He fights with more sincerity than technique and somehow that keeps working.'],

            ['name' => 'Anya Forger', 'cat' => 'anime', 'fandom' => 'Spy x Family', 'content' => 'spy-x-family', 'role' => 'Protagonist',
                'gender' => 'Female', 'age' => '6', 'affiliation' => 'Penguin Park / Eden School',
                'abilities' => 'Telepathy, Dramatic flair, Star-shaped clasps', 'debut' => 'Chapter 1',
                'views' => 341000, 'pop' => 96, 'featured' => false, 'gallery' => false,
                'bio' => 'A telepath adopted for a mission she was never meant to understand, now playing along because the stakes involve star stickers. She hears every lie in the room and repeats the wrong half of them.'],

            ['name' => 'Eren Yeager', 'cat' => 'anime', 'fandom' => 'Attack on Titan: The Final Season', 'content' => 'attack-on-titan-the-final-season', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => '19', 'affiliation' => 'Survey Corps (formerly)',
                'abilities' => 'Attack Titan, Founding Titan, Hardening', 'debut' => 'Chapter 1',
                'views' => 447000, 'pop' => 92, 'featured' => false, 'gallery' => false,
                'bio' => 'Started swearing at monsters behind a wall and ended up the thing his friends feared most. The series uses his eyes to track exactly where the idealism leaked out.'],

            ['name' => 'Malenia, Blade of Miquella', 'cat' => 'gaming', 'fandom' => 'Elden Ring', 'content' => 'elden-ring', 'role' => 'Antagonist',
                'gender' => 'Female', 'age' => 'Immortal', 'affiliation' => 'Haligtree',
                'abilities' => 'Waterfowl Dance, Scarlet Rot, Prosthetic blade arts', 'debut' => 'Boss fight, Elphael',
                'views' => 372000, 'pop' => 97, 'featured' => true,
                'gallery' => true,
                'bio' => 'A swordswoman missing half her body who still out-dances everything in the Lands Between. Players have memorised her attack strings the way choirs memorise hymns — mostly by dying in verse.'],

            ['name' => 'Zagreus', 'cat' => 'gaming', 'fandom' => 'Hades II', 'content' => 'hades-ii', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => 'Ageless', 'affiliation' => 'House of Hades',
                'abilities' => 'Infernal Arms, God boons, Death Defiance', 'debut' => 'First escape attempt',
                'views' => 214000, 'pop' => 88, 'featured' => false, 'gallery' => false,
                'bio' => 'The prince of the underworld who treats each failed escape as rehearsal. He argues with his father in the same breath as he arms himself for another run.'],

            ['name' => 'Melinoë', 'cat' => 'gaming', 'fandom' => 'Hades II', 'content' => 'hades-ii', 'role' => 'Protagonist',
                'gender' => 'Female', 'age' => 'Ageless', 'affiliation' => 'Witch of the Crossroads',
                'abilities' => 'Moon witchcraft, Incantations, Saturnic hexes', 'debut' => 'Tutorial night',
                'views' => 186000, 'pop' => 85, 'featured' => false, 'gallery' => false,
                'bio' => 'Raised in the crossroads to hunt the Titan of Time, she cycles the same night until the mission is done. Her spells are scheduled like rituals because witches keep calendars too.'],

            ['name' => 'Astarion', 'cat' => 'gaming', 'fandom' => "Baldur's Gate 3", 'content' => 'baldurs-gate-3', 'role' => 'Supporting',
                'gender' => 'Male', 'age' => 'Over 200', 'affiliation' => 'Camp party',
                'abilities' => 'Vampire spawn bite, Sneak attack, Enchantment magic', 'debut' => 'Act 1, Wilderness',
                'views' => 267000, 'pop' => 91, 'featured' => false, 'gallery' => false,
                'bio' => 'A rogue vampire spawn with two centuries of resentment and one very good exit line. His approval system rewards chaos, but his personal quest quietly demands the opposite.'],

            ['name' => 'Madeline', 'cat' => 'gaming', 'fandom' => 'Celeste', 'content' => 'celeste', 'role' => 'Protagonist',
                'gender' => 'Female', 'age' => '20s', 'affiliation' => 'Solo climber',
                'abilities' => 'Dash, Wall jump, Stamina', 'debut' => 'Trailhead',
                'views' => 98000, 'pop' => 76, 'featured' => false, 'gallery' => false,
                'bio' => 'Climbing a mountain to prove a point to herself, with an anxiety that speaks in her own voice. Her movement set is six inputs wide and ten years deep in speedrun tech.'],

            ['name' => 'Miles Morales', 'cat' => 'movies', 'fandom' => 'Spider-Man: Across the Spider-Verse', 'content' => 'spider-man-across-the-spider-verse', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => '15', 'affiliation' => 'Spider-Verse / Brooklyn',
                'abilities' => 'Venom strike, Camouflage, Improvised webbing', 'debut' => 'Miles Morales: Spider-Man',
                'views' => 403000, 'pop' => 97, 'featured' => true, 'gallery' => false,
                'bio' => 'Brooklyn\'s Spider-Man, still negotiating curfew with his parents and canon with the multiverse. His answer to "everyone has a destiny scene" is a flat refusal.'],

            ['name' => 'Paul Atreides', 'cat' => 'movies', 'fandom' => 'Dune: Part Two', 'content' => 'dune-part-two', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => '18-20', 'affiliation' => 'House Atreides / Fremen',
                'abilities' => 'Bene Gesserit training, Prescience, Worm riding', 'debut' => 'Part One',
                'views' => 318000, 'pop' => 90, 'featured' => false, 'gallery' => false,
                'bio' => 'An heir who walked into the desert and came out holding a prophecy he did not write. The film tracks his certainty growing into something far harder to stop.'],

            ['name' => 'Aang', 'cat' => 'tv-shows', 'fandom' => 'Avatar: The Last Airbender', 'content' => 'avatar-the-last-airbender', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => '112 (appears 12)', 'affiliation' => 'Team Avatar',
                'abilities' => 'Airbending, Avatar State, Energybending', 'debut' => 'Episode 1',
                'views' => 428000, 'pop' => 95, 'featured' => false, 'gallery' => false,
                'bio' => 'A hundred years behind schedule and still the only person who can fix it. He solves problems with an air scooter first and philosophy second, and it usually works.'],

            ['name' => 'Mark Scout', 'cat' => 'tv-shows', 'fandom' => 'Severance', 'content' => 'severance', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => '40s', 'affiliation' => 'Lumon Industries, Macrodata Refinement',
                'abilities' => 'Macrodata sorting, Repressed composure, Hallway endurance', 'debut' => 'Episode 1',
                'views' => 246000, 'pop' => 87, 'featured' => false, 'gallery' => false,
                'bio' => 'A man whose work self and home self have never met, slowly comparing notes through severed messages. He keeps his face still while the show rearranges everything under it.'],

            ['name' => 'Monkey D. Luffy', 'cat' => 'manga', 'fandom' => 'One Piece', 'content' => 'one-piece', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => '19', 'affiliation' => 'Straw Hat Pirates',
                'abilities' => 'Gum-Gum fruit powers, Haki, Boundless appetite', 'debut' => 'Chapter 1',
                'views' => 481000, 'pop' => 99, 'featured' => true, 'gallery' => false,
                'bio' => 'A rubber pirate with a captain\'s hat and a crew he recruited by taking their dreams as seriously as his own. He negotiates with fists and repays debts with feasts.'],

            ['name' => 'Roronoa Zoro', 'cat' => 'manga', 'fandom' => 'One Piece', 'content' => 'one-piece', 'role' => 'Supporting',
                'gender' => 'Male', 'age' => '19', 'affiliation' => 'Straw Hat Pirates',
                'abilities' => 'Three-sword style, Armament Haki, Unshakeable resolve', 'debut' => 'Chapter 3',
                'views' => 352000, 'pop' => 92, 'featured' => false, 'gallery' => false,
                'bio' => 'A pirate hunter who swore never to lose again and then lost anyway, which is the point. His sense of direction is legendary mainly for the wrong reasons.'],

            ['name' => 'Denji', 'cat' => 'manga', 'fandom' => 'Chainsaw Man', 'content' => 'chainsaw-man', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => '16-17', 'affiliation' => 'Public Safety, Tokyo Special Division 4',
                'abilities' => 'Chainsaw transformation, Devil contracts, Resilience', 'debut' => 'Chapter 1',
                'views' => 289000, 'pop' => 90, 'featured' => false, 'gallery' => false,
                'bio' => 'A debt-ridden teen whose dream list fits on a receipt. The series keeps handing him exactly what he asked for and letting him discover the invoice.'],

            ['name' => 'Batman (Bruce Wayne)', 'cat' => 'comics', 'fandom' => 'Batman: Court of Owls', 'content' => 'batman-court-of-owls', 'role' => 'Protagonist',
                'gender' => 'Male', 'age' => '30s', 'affiliation' => 'Wayne Enterprises / Gotham',
                'abilities' => 'Detective work, Gadgets, Peak human conditioning', 'debut' => 'Detective Comics #27',
                'views' => 334000, 'pop' => 94, 'featured' => false, 'gallery' => false,
                'bio' => 'Gotham\'s self-appointed auditor, convinced he had memorised every street. The Owls arc exists to prove the city had a wing he never opened.'],
        ];

        foreach ($rows as $row) {
            $character = Character::query()->updateOrCreate(
                ['slug' => $this->uniqueSlug('characters', $row['name'])],
                [
                    'name' => $row['name'],
                    'category_id' => $categories[$row['cat']],
                    'fandom' => $row['fandom'],
                    'bio' => $row['bio'],
                    'gender' => $row['gender'],
                    'age' => $row['age'],
                    'affiliation' => $row['affiliation'],
                    'abilities' => $row['abilities'],
                    'debut' => $row['debut'],
                    'views_count' => $row['views'],
                    'popularity' => $row['pop'],
                    'featured' => $row['featured'],
                    'image' => null,
                    'gallery' => $row['gallery']
                        ? [ph($row['name'].' portrait 1', 640, 640, $row['cat']), ph($row['name'].' portrait 2', 640, 640, $row['cat']), ph($row['name'].' portrait 3', 640, 640, $row['cat'])]
                        : null,
                ]
            );

            if (isset($contents[$row['content']])) {
                ContentCharacter::query()->firstOrCreate(
                    ['content_id' => $contents[$row['content']], 'character_id' => $character->id],
                    ['role' => $row['role']]
                );
            }
        }

        $byName = Character::query()->pluck('id', 'name');

        $relations = [
            ['Tanjiro Kamado', 'Nezuko Kamado', 'sibling'],
            ['Nezuko Kamado', 'Tanjiro Kamado', 'sibling'],
            ['Satoru Gojo', 'Yuji Itadori', 'mentor'],
            ['Yuji Itadori', 'Satoru Gojo', 'ally'],
            ['Zagreus', 'Melinoë', 'sibling'],
            ['Melinoë', 'Zagreus', 'sibling'],
            ['Monkey D. Luffy', 'Roronoa Zoro', 'ally'],
            ['Roronoa Zoro', 'Monkey D. Luffy', 'ally'],
        ];

        foreach ($relations as [$from, $to, $relation]) {
            if (isset($byName[$from], $byName[$to])) {
                CharacterRelation::query()->firstOrCreate(
                    ['character_id' => $byName[$from], 'related_character_id' => $byName[$to]],
                    ['relation' => $relation]
                );
            }
        }

        // Volume rows via the factory (category pinned so no stray categories appear).
        Character::factory()->count(3)->create([
            'category_id' => $categories->random(),
        ]);
    }
}
