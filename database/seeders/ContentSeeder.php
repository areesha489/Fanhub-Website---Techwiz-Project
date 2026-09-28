<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Content;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\Concerns\UniqueSlug;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    use UniqueSlug;

    /**
     * Catalogue titles: curated marquee records across all eight categories
     * plus a few factory rows for volume. Two entries stay drafts.
     */
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');
        $adminId = User::query()->where('email', 'admin@fanhubplus.test')->value('id');
        $tagIds = Tag::query()->pluck('id', 'name');

        $rows = [
            ['title' => 'Demon Slayer: Kimetsu no Yaiba', 'cat' => 'anime', 'type' => 'Series', 'genre' => 'Action', 'date' => '2019-04-06', 'status' => 'published', 'rating' => 8.8, 'views' => 486000, 'pop' => 98, 'featured' => true, 'trailer' => 'https://www.youtube.com/watch?v=VQGCKyvzIM4',
                'tags' => ['Shonen', 'Dark Fantasy', 'OST', 'Dubbed'],
                'description' => 'A demon hunter walks a Taisho-era road with his sister at his side and a breathing style in his lungs. Sword work, family bonds and water that behaves like calligraphy.',
                'synopsis' => 'After a night of slaughter leaves his family broken, Tanjiro Kamado joins the Demon Slayer Corps to find a way back to the sister the demons spared. Each mission is a duel decided in a single exhale, and every opponent carries a grievance of their own. The series keeps its warmth even as its fights grow crueler.'],

            ['title' => 'Frieren: Beyond Journey\'s End', 'cat' => 'anime', 'type' => 'Series', 'genre' => 'Fantasy', 'date' => '2023-09-29', 'status' => 'published', 'rating' => 9.1, 'views' => 372000, 'pop' => 95, 'featured' => true, 'trailer' => 'https://www.youtube.com/watch?v=Iwr1aLEDpe4',
                'tags' => ['Seinen', 'Deep Dive', 'OST'],
                'description' => 'An elf mage outlives the heroes she travelled with and finally starts paying attention to the decades she skipped. A fantasy told backwards from everyone else\'s ending.',
                'synopsis' => 'Decades after the party that defeated the Demon King disbands, Frieren realizes how little she learned about the humans beside her. She retraces the old road, this time stopping for the small conversations she once considered beneath her. The show treats time itself as the antagonist. Quiet episodes land harder than most finales.'],

            ['title' => 'Attack on Titan: The Final Season', 'cat' => 'anime', 'type' => 'Series', 'genre' => 'Action', 'date' => '2020-12-07', 'status' => 'published', 'rating' => 9.3, 'views' => 498000, 'pop' => 96, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=MUCN-JwUvbY',
                'tags' => ['Shonen', 'Villains', 'OST'],
                'description' => 'The walls come down and the story stops pretending there were ever good sides to pick. A finale arc that turns its own heroes into the question.',
                'synopsis' => 'The last season drags its characters past the border and into the history that created their enemies. Alliances formed in desperation fracture under scrutiny, and the series refuses to hand anyone a clean conscience. Every victory costs more than the one before it. By the end the audience is asked to judge people it once rooted for.'],

            ['title' => 'Jujutsu Kaisen', 'cat' => 'anime', 'type' => 'Series', 'genre' => 'Action', 'date' => '2020-10-03', 'status' => 'published', 'rating' => 8.9, 'views' => 441000, 'pop' => 94, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=pkKu9hLT-t8',
                'tags' => ['Shonen', 'Dark Fantasy', 'Dubbed'],
                'description' => 'Cursed energy, school clubs and a finger-eating worst case scenario. The shonen formula run at maximum pressure with jokes that land between the hits.',
                'synopsis' => 'Yuji Itadori swallows a cursed relic to save his friends and becomes the vessel his school would rather contain. Instead of rotting in a cell he trains, because the sorcerers need bodies and he has already seen what happens when nobody moves. Fights are choreographed like arguments, each technique answering the last. Even its comic relief carries dread.'],

            ['title' => 'Spy x Family', 'cat' => 'anime', 'type' => 'Series', 'genre' => 'Comedy', 'date' => '2022-04-09', 'status' => 'published', 'rating' => 8.4, 'views' => 289000, 'pop' => 88, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=fqWKCELEdYM',
                'tags' => ['Found Family', 'Slice of Life', 'Dubbed'],
                'description' => 'A spy, an assassin and a telepath build a fake family so convincingly it starts working. Domestic comedy staged against a cold war that neither of the adults mentions.',
                'synopsis' => 'Agent Twilight must enroll a child in an elite school to reach his target, so he adopts the first orphan who fits the role — without knowing she reads minds. His new wife is a far better killer than his new husband, and neither is who the other believes. Meanwhile the daughter just wants everyone to get along at the school festival. The tension comes from how close the truth sits at the dinner table.'],

            ['title' => 'Neon Genesis Evangelion: Rebuild Retrospective', 'cat' => 'anime', 'type' => 'Other', 'genre' => 'Sci-Fi', 'date' => '2026-01-15', 'status' => 'draft', 'rating' => 7.2, 'views' => 5400, 'pop' => 45, 'featured' => false, 'trailer' => null,
                'tags' => ['Deep Dive', 'Interview'],
                'description' => 'A long read tracing how four films quietly rewrote a television ending two decades older. Draft — not yet published.',
                'synopsis' => 'This piece compares the Rebuild finale against the original broadcast ending and asks what changed when the budget and the expectations both grew. It is still missing the interview section and two comparison screenshots. Editors have requested a shorter opening.'],

            ['title' => 'Elden Ring', 'cat' => 'gaming', 'type' => 'Game', 'genre' => 'RPG', 'date' => '2022-02-25', 'status' => 'published', 'rating' => 9.4, 'views' => 452000, 'pop' => 97, 'featured' => true, 'trailer' => 'https://www.youtube.com/watch?v=E3Huy2cdih0',
                'tags' => ['Open World', 'Speedrun', 'OST'],
                'description' => 'A shattered kingdom, a mounted corpse and the best horse in games. Exploration is the reward system, and death is just punctuation.',
                'synopsis' => 'The Tarnished crosses a land where the demigods broke the ring and each hoards a fragment of its power. Nothing marks the interesting direction on the map; you ride toward whatever silhouette catches the light. Build variety keeps fresh runs inventing themselves years later. Every ruin hides either a story or a trap, usually both.'],

            ['title' => 'Hades II', 'cat' => 'gaming', 'type' => 'Game', 'genre' => 'Roguelike', 'date' => '2024-05-06', 'status' => 'published', 'rating' => 8.9, 'views' => 213000, 'pop' => 90, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=U8lJRcUeEMs',
                'tags' => ['Roguelike', 'Indie', 'Narrative'],
                'description' => 'Melinoë raids the underworld on a schedule, and every failed attempt still advances the plot. The rare roguelike where dying counts as working overtime.',
                'synopsis' => 'The princess of the underworld arms herself with witchcraft and divine blessings to stop the Titan of Time. Runs loop through hand-built regions that shift their threats each night, and the cast remembers every conversation you earned between deaths. Romance, rivalry and family politics all progress without a single cutscene skipping ahead. The early access build already runs deeper than most finished games.'],

            ['title' => 'Baldur\'s Gate 3', 'cat' => 'gaming', 'type' => 'Game', 'genre' => 'RPG', 'date' => '2023-08-03', 'status' => 'published', 'rating' => 9.6, 'views' => 398000, 'pop' => 95, 'featured' => true, 'trailer' => 'https://www.youtube.com/watch?v=XuCfkgaaa08',
                'tags' => ['Narrative', 'Found Family', 'Villains'],
                'description' => 'A mind flayer tadpole, a camp full of misfits and dice rolls that will betray you at the worst possible moment. tabletop rules turned into a soap opera you can lose.',
                'synopsis' => 'Kidnapped and implanted, the party races toward a cure while each member drags their own unfinished business into every long rest. The game says yes to choices most RPGs only animate, including the ones that end companions early. Combat rewards preparation over levels, so a well-placed shove beats a grind. Honour mode exists for players who think the dice owe them nothing.'],

            ['title' => 'Celeste', 'cat' => 'gaming', 'type' => 'Game', 'genre' => 'Platformer', 'date' => '2018-01-25', 'status' => 'published', 'rating' => 9.0, 'views' => 96000, 'pop' => 78, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=FqBj2IGg6Uw',
                'tags' => ['Indie', 'Speedrun', 'Narrative'],
                'description' => 'A mountain, a Coyote Time window of a few generous pixels and a girl arguing with her own reflection. Difficulty options that never feel like an apology.',
                'synopsis' => 'Madeline climbs Celeste Mountain to prove something to herself and meets versions of her anxiety that would rather she stayed at the base camp. The platforming asks for precision but never for luck, and assist options let players retune the challenge without breaking its rhythm. Speedrunners have long since turned its movement tech into a language. It remains one of the kindest hard games ever shipped.'],

            ['title' => 'Hollow Signal', 'cat' => 'gaming', 'type' => 'Game', 'genre' => 'Thriller', 'date' => '2026-03-20', 'status' => 'draft', 'rating' => 6.8, 'views' => 5100, 'pop' => 42, 'featured' => false, 'trailer' => null,
                'tags' => ['Indie', 'Narrative'],
                'description' => 'Coverage draft for an unannounced analog-horror indie about a radio tower that answers back. Held back until the studio lifts the embargo.',
                'synopsis' => 'The studio has only shared a teaser page and three frames of tape hiss, so this entry waits in draft until the embargo date. We have an early build impression ready to publish and a developer Q&A half transcribed. Status will move to published the moment the announcement lands.'],

            ['title' => 'Spider-Man: Across the Spider-Verse', 'cat' => 'movies', 'type' => 'Movie', 'genre' => 'Action', 'date' => '2023-06-02', 'status' => 'published', 'rating' => 9.2, 'views' => 421000, 'pop' => 96, 'featured' => true, 'trailer' => 'https://www.youtube.com/watch?v=shW9i6k8cB0',
                'tags' => ['Animation', 'Superheroes', 'Fan Art'],
                'description' => 'Every universe has its own art direction and none of them agree on the rules. A cliffhanger that broke fan timelines for a summer.',
                'synopsis' => 'Miles Morales swings into a society of Spider-People policing events that supposedly must happen. When he refuses to accept a sacrifice someone else wrote for him, the multiverse splits into factions. Each dimension is drawn in a different medium, from ink wash to LEGO, and the differences carry character. The film ends mid-argument on purpose.'],

            ['title' => 'Dune: Part Two', 'cat' => 'movies', 'type' => 'Movie', 'genre' => 'Sci-Fi', 'date' => '2024-03-01', 'status' => 'published', 'rating' => 9.0, 'views' => 356000, 'pop' => 92, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=Way9Dexny3w',
                'tags' => ['Narrative', 'OST', 'Deep Dive'],
                'description' => 'Desert politics, sandworm logistics and a prophecy being weaponised in real time. A blockbuster that trusts the audience to keep score.',
                'synopsis' => 'Paul Atreides joins the Fremen and discovers that vengeance and messiahhood are the same machine from opposite ends. The film stages its wars like documentaries and its rituals like warnings. Underneath the scale sits a study of how movements choose their symbols. By the final ride the cheering and the dread arrive together.'],

            ['title' => 'Inside Out 2', 'cat' => 'movies', 'type' => 'Movie', 'genre' => 'Adventure', 'date' => '2024-06-14', 'status' => 'published', 'rating' => 8.1, 'views' => 187000, 'pop' => 84, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=LEjhY15eCx0',
                'tags' => ['Animation', 'Dubbed'],
                'description' => 'Puberty moves into headquarters and evicts the furniture. The sequel about anxiety that an entire generation needed to see with their parents.',
                'synopsis' => 'Riley turns thirteen just as her control panel is replaced and a new set of emotions shows up to redecorate. Joy and her crew get exiled to the back of the mind while Anxiety runs the console. The jokes land fast but the argument underneath — that growing up means making room for feelings you did not invite — is played straight. The memory dump scene earned every tissue in the theatre.'],

            ['title' => 'Everything Everywhere All at Once', 'cat' => 'movies', 'type' => 'Movie', 'genre' => 'Action', 'date' => '2022-03-25', 'status' => 'published', 'rating' => 8.7, 'views' => 245000, 'pop' => 87, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=wxN1T1uxQ2g',
                'tags' => ['Indie', 'Narrative', 'Superheroes'],
                'description' => 'A laundromat audit becomes an interdimensional war fought with hot dog fingers and generational grief. Absurdism deployed entirely in service of a family argument.',
                'synopsis' => 'Evelyn Wang is mid-IRS interview when a version of herself from another universe recruits her against a threat spanning all of them. To fight, she must job-hopping through lives she could have lived — and every one of them reframes what she abandoned. The martial arts is inventive, the jokes are genuinely strange, and the emotional target never moves. It won its Oscars for a reason that survives the memes.'],

            ['title' => 'Severance', 'cat' => 'tv-shows', 'type' => 'TV Show', 'genre' => 'Thriller', 'date' => '2022-02-18', 'status' => 'published', 'rating' => 9.2, 'views' => 334000, 'pop' => 93, 'featured' => true, 'trailer' => 'https://www.youtube.com/watch?v=xEQP4VVuyrY',
                'tags' => ['Noir', 'Narrative', 'Deep Dive'],
                'description' => 'Office workers surgically split into a self that remembers nothing and a self that never leaves the building. Corporate horror with fluorescent lighting and a marching band.',
                'synopsis' => 'Employees at Lumon agree to a procedure that severs their work memories from their outside lives, then slowly notice how little their two halves agree on. The show builds its mystery from minuscule details — a floor plan quietly redrawn, a number nobody explains. Season two widens the breach between the two worlds. Every hallway shot feels slightly wrong on purpose.'],

            ['title' => 'The Bear', 'cat' => 'tv-shows', 'type' => 'TV Show', 'genre' => 'Drama', 'date' => '2022-06-23', 'status' => 'published', 'rating' => 8.6, 'views' => 268000, 'pop' => 88, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=ojjCvICC86c',
                'tags' => ['Slice of Life', 'Narrative'],
                'description' => 'A fine-dining chef inherits a beef sandwich shop and a grief-stricken family crew. Episodes shot like tickets printing and a kitchen you can smell.',
                'synopsis' => 'Carmy returns to Chicago to run his late brother\'s restaurant and finds a ledger of debts, a staff that runs on habit and a walk-in freezer full of arguments. The series treats cooking as craft under pressure rather than glamour. Its loudest episodes are also its most tender. Every character is one service away from either a breakthrough or a walkout.'],

            ['title' => 'Fallout', 'cat' => 'tv-shows', 'type' => 'TV Show', 'genre' => 'Sci-Fi', 'date' => '2024-04-10', 'status' => 'published', 'rating' => 8.4, 'views' => 297000, 'pop' => 89, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=f3yQVMtx980',
                'tags' => ['Retro', 'Collectible', 'Dubbed'],
                'description' => 'A vault dweller surfaces into a wasteland that kept its 1950s optimism and lost everything else. The adaptation that finally understood the games\' jokes.',
                'synopsis' => 'Lucy leaves the only home she has known to find her father and meets wastelanders whose morals were forged above ground. Parallel timelines reveal how the corporations wrote their own apocalypse. The production design treats cassettes and chrome as serious archaeology. Violence lands with the same dark shrug the games use.'],

            ['title' => 'Avatar: The Last Airbender', 'cat' => 'tv-shows', 'type' => 'TV Show', 'genre' => 'Adventure', 'date' => '2005-02-21', 'status' => 'published', 'rating' => 9.5, 'views' => 476000, 'pop' => 94, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=ByAn8DF8Ykk',
                'tags' => ['Found Family', 'Dubbed', 'OST'],
                'description' => 'Four nations, one reincarnated kid and a three-season plan that actually sticks the landing. Still the benchmark for animated adventure storytelling.',
                'synopsis' => 'Aang wakes after a century under the ice to a world at war and a prophecy that expects him to end it before he learns the other three elements. The journey assembles a team that argues like siblings and grows like a family. Its villains receive more nuance than most shows grant their heroes. The finale resolves both the war and the character work without cheating either.'],

            ['title' => 'NewJeans — "Get Up"', 'cat' => 'k-pop', 'type' => 'Album', 'genre' => 'Pop', 'date' => '2023-07-21', 'status' => 'published', 'rating' => 8.5, 'views' => 156000, 'pop' => 86, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=jOTfBlKSQYY',
                'tags' => ['K-Pop', 'Comeback', 'OST'],
                'description' => 'Six weeks of teaser art, a cartoon collaboration and hooks built for summer windows down. A mini album that ran the season.',
                'synopsis' => 'The EP sequences upbeat confession songs against a comic-book campaign that gave every member an animated alter ego. Production leans on Y2K R&B revival without borrowing any one era whole. Its title track choreography spread across practice rooms on three continents within a week. The B-sides carry the same tempo discipline as the singles.'],

            ['title' => 'BTS — "Proof"', 'cat' => 'k-pop', 'type' => 'Album', 'genre' => 'Pop', 'date' => '2022-06-10', 'status' => 'published', 'rating' => 8.3, 'views' => 209000, 'pop' => 85, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=kXpOEzNZ8hQ',
                'tags' => ['K-Pop', 'Comeback', 'Collectible'],
                'description' => 'A decade-spanning anthology with new tracks stapled to the memories fans already owned. The physical editions became their own collector market.',
                'synopsis' => 'Three discs walk from the group\'s debut material through the era that made them global, closing with songs written for the fans who kept the archives. The new material reflects on growth without pretending the old songs were embarrassing. Photobook versions sold through pre-order windows in hours. It works both as a summary and a door for new listeners.'],

            ['title' => 'Stray Kids — "5-STAR"', 'cat' => 'k-pop', 'type' => 'Album', 'genre' => 'Pop', 'date' => '2024-07-19', 'status' => 'published', 'rating' => 8.6, 'views' => 142000, 'pop' => 84, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=JsOOis4bBFg',
                'tags' => ['K-Pop', 'Concert', 'Comeback'],
                'description' => 'Loud, percussive and built for arena ceilings — the group\'s loudest era with its most polished B-sides. Tour choreography baked into the mixing.',
                'synopsis' => 'The title track stacks distorted bass under a chant section designed to be screamed back live. Album cuts swing between aggression and a ballad that slows everything down on purpose. Comeback stages doubled as rehearsal for the stadium run that followed. Fan projects coordinated lightstick colours for every track.'],

            ['title' => 'Saga, Vol. 1', 'cat' => 'comics', 'type' => 'Chapter', 'genre' => 'Sci-Fi', 'date' => '2012-03-14', 'status' => 'published', 'rating' => 9.0, 'views' => 88000, 'pop' => 80, 'featured' => false, 'trailer' => null,
                'tags' => ['Found Family', 'Narrative', 'Fan Art'],
                'description' => 'Two soldiers from opposite sides of a war fall in love and learn the galaxy wants a say in their baby\'s future. Space opera told like a bedtime story with teeth.',
                'synopsis' => 'Hazmat-bound enemies Alana and Marko desert with an infant neither side will forgive them for raising. The opening arc establishes a universe where romance, war story and flat-out weirdness share a panel without friction. Narration by the child reframes adult failures as future legends. It remains one of the easiest comics to hand a newcomer.'],

            ['title' => 'Batman: Court of Owls', 'cat' => 'comics', 'type' => 'Chapter', 'genre' => 'Mystery', 'date' => '2011-11-02', 'status' => 'published', 'rating' => 8.8, 'views' => 134000, 'pop' => 83, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=qtdJl6ccK18',
                'tags' => ['Superheroes', 'Noir', 'Villains'],
                'description' => 'Gotham proves older than its protector — and the family running it has been watching from the walls. A mystery that puts Batman in a maze before it puts him in a fight.',
                'synopsis' => 'A series of impossible murders forces Bruce Wayne to admit he does not know his own city\'s history. The Court of Owls interrupts his certainty with architecture, poetry and trained killers. Twisted labyrinth sequences rewired how the title handled perspective. It rebuilt the Bat-myth around the idea that legacy cuts both ways.'],

            ['title' => 'Watchmen', 'cat' => 'comics', 'type' => 'Chapter', 'genre' => 'Drama', 'date' => '1987-09-01', 'status' => 'published', 'rating' => 9.4, 'views' => 176000, 'pop' => 82, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=bdUESyHY8go',
                'tags' => ['Superheroes', 'Noir', 'Deep Dive'],
                'description' => 'Retired costumed heroes, a dead vigilante and a Doomsday clock nobody resets. The series that made deconstruction into a genre of its own.',
                'synopsis' => 'A murder investigation drags masked veterans back toward the ideology they abandoned. Between chapters, fictional documents and comic-within-comic sequences widen the argument. The question it keeps asking is whether any of these people were ever well. Decades later, arguments about it still start fights at convention panels.'],

            ['title' => 'One Piece', 'cat' => 'manga', 'type' => 'Series', 'genre' => 'Adventure', 'date' => '1997-07-22', 'status' => 'published', 'rating' => 9.7, 'views' => 495000, 'pop' => 99, 'featured' => true, 'trailer' => 'https://www.youtube.com/watch?v=Ades3pQbeh8',
                'tags' => ['Shonen', 'Found Family', 'Deep Dive'],
                'description' => 'A rubber pirate, a crew of specialists and a world map still unfinished after twenty-five years of chapters. The long game that keeps rewarding patience.',
                'synopsis' => 'Monkey D. Luffy sails toward the treasure that will make him King of the Pirates, gathering crewmates whose dreams are as unreasonable as his own. Every island resolves its own story while feeding a larger history the readers only assemble in hindsight. The world-building is dense enough to support encyclopedias. New readers routinely underestimate how fast it hooks them.'],

            ['title' => 'Chainsaw Man', 'cat' => 'manga', 'type' => 'Series', 'genre' => 'Action', 'date' => '2018-12-03', 'status' => 'published', 'rating' => 8.9, 'views' => 341000, 'pop' => 91, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=tAzAhDNdehs',
                'tags' => ['Shonen', 'Dark Fantasy', 'Villains'],
                'description' => 'A debt-ridden teen merges with his chainsaw dog and takes the worst job offer in hell. Panel pacing that swings from slapstick to silence in a single page.',
                'synopsis' => 'Denji trades his ordinary life for a shot at toast with jam on it and is promptly drafted into a devil-hunting agency. The series frames horror through bureaucracy and hunger, then stages set pieces that read like stunt work. Movie-poster chapter titles became a fandom game of their own. Its restraint with exposition makes the brutal pages hit harder.'],

            ['title' => 'Cosplay Championship: Craftsmanship Finals', 'cat' => 'cosplay', 'type' => 'Other', 'genre' => 'Craft', 'date' => '2025-08-16', 'status' => 'published', 'rating' => 8.7, 'views' => 42000, 'pop' => 70, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=QqBe8EndbLI',
                'tags' => ['Convention', 'Craft', 'Fan Art'],
                'description' => 'Full-length coverage of the craftsmanship track — build logs, judge notes and the interviews backstage before stage call. Photos from the floor.',
                'synopsis' => 'Contestants present armour, wigs and sewn sets to a panel that asks about foam density as happily as aesthetics. Our write-up follows three builders from rough pattern to final walk. The appendix lists common judge questions first-time entrants forget to prepare. Coverage will refresh with the next regional qualifier.'],

            ['title' => 'Foam & Fabric: Armor Basics', 'cat' => 'cosplay', 'type' => 'Other', 'genre' => 'Tutorial', 'date' => '2025-03-05', 'status' => 'published', 'rating' => 7.9, 'views' => 26000, 'pop' => 62, 'featured' => false, 'trailer' => 'https://www.youtube.com/watch?v=dN3NHy7Asqc',
                'tags' => ['Craft', 'Cosplay', 'Deep Dive'],
                'description' => 'A beginner path through EVA foam and heavy cotton — which glue lies to you, which sealant survives a convention floor. Tool list included.',
                'synopsis' => 'The guide opens with the four tools that actually matter and skips the expensive ones until budgeting makes sense. Sections cover patterning, heat shaping, sealing and a weathering pass that hides seams. Each stage ends with the mistakes beginners make and how to reverse them. Printable checklists sit at the end of every chapter.'],
        ];

        foreach ($rows as $row) {
            $existing = Content::query()->where('title', $row['title'])->first();

            $content = Content::query()->updateOrCreate(
                ['slug' => $existing?->slug ?? $this->uniqueSlug('contents', $row['title'])],
                [
                    'title' => $row['title'],
                    'category_id' => $categories[$row['cat']],
                    'content_type' => $row['type'],
                    'genre' => $row['genre'],
                    'description' => $row['description'],
                    'synopsis' => $row['synopsis'],
                    'cover_image' => null,
                    'banner_image' => null,
                    'release_date' => $row['date'],
                    'status' => $row['status'],
                    'rating' => $row['rating'],
                    'views_count' => $row['views'],
                    'popularity' => $row['pop'],
                    'featured' => $row['featured'],
                    'trailer_url' => $row['trailer'],
                    'created_by' => $adminId,
                ]
            );

            $ids = collect($row['tags'])
                ->map(fn (string $name): ?int => $tagIds[$name] ?? null)
                ->filter()
                ->values()
                ->all();

            $content->tags()->sync($ids);
        }

        // Volume rows via A1's factory (category pinned so no stray categories
        // appear; trailer left null so no broken embeds ship).
        Content::factory()->count(4)->create([
            'category_id' => $categories->random(),
            'rating' => fake()->randomFloat(1, 6.5, 9.8),
            'views_count' => fake()->numberBetween(5000, 500000),
            'trailer_url' => null,
            'created_by' => $adminId,
        ]);
    }
}
