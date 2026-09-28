<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\Concerns\UniqueSlug;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    use UniqueSlug;

    /**
     * Ten curated editorial pieces with rich HTML bodies, varied authors and
     * two featured, plus a couple of factory rows for volume.
     */
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');
        $authors = User::query()->pluck('id', 'email');

        $rows = [
            ['title' => 'Why Seasonal Charts Matter More Than Ever', 'cat' => 'anime', 'author' => 'priya@fanhubplus.test',
                'days' => 12, 'views' => 148000, 'featured' => true,
                'excerpt' => 'Twelve episodes, three months, forty competing openers — the seasonal chart has become the fandom\'s shared calendar.',
                'body' => '<p>Simulcast season turned anime into an appointment. When forty shows premiere inside a fortnight, viewers need a triage system, and the community-built chart became exactly that — a living document argued over in comment threads before episode two even airs.</p><h2>A shared clock</h2><p>Charts matter because they synchronise attention. A show that trends alone for a week disappears; a show that trends alongside twelve rivals gets clipped, gif-ed and defended in the same hour. The chart gives every premiere the same stage and lets word of mouth do the rest.</p><ul><li>Week one is vibes — animation quality and voice direction set the tone.</li><li>Week four is structure — writers reveal whether the arc has a spine.</li><li>Week eight is reputation — the shows people admit to watching out loud.</li></ul><p>None of this replaces criticism. It just means the conversation starts earlier and finishes louder than it used to.</p>'],

            ['title' => 'The Quiet Work of the Found-Family Trope', 'cat' => 'tv-shows', 'author' => 'sofia@fanhubplus.test',
                'days' => 27, 'views' => 96000, 'featured' => false,
                'excerpt' => 'Found family endures because it answers a question prestige drama keeps asking: who actually shows up when the plot gets heavy?',
                'body' => '<p>The trope works when it is load-bearing rather than decorative. A group of misfits sharing a kitchen is charming for one episode; the same group covering each other\'s shifts, arguing about rent and quietly restructuring their plans is a season of character work.</p><h2>Why writers keep returning</h2><p>Found family lets a story externalise interior growth. Instead of narrating that a character has learned to trust, you let them hand someone else the keys — literally, in a heist episode, and figuratively in the one after.</p><p>The best versions also let the family disagree. Fracture is the proof the bonds are real: people only argue like that when leaving is on the table and staying is a choice.</p><ul><li>The mentor who stops mentoring and starts listening.</li><li>The rival who shows up early with supplies.</li><li>The straggler who, at some point, locks the door behind them.</li></ul>'],

            ['title' => 'Speedrunning as Fandom Culture', 'cat' => 'gaming', 'author' => 'marcus@fanhubplus.test',
                'days' => 41, 'views' => 172000, 'featured' => true,
                'excerpt' => 'A speedrun is a love letter written in frame data — obsessive, competitive and strangely communal.',
                'body' => '<p>Ask a speedrunner why they reset for six hours and you rarely hear "because it is fast." You hear about the skip that broke the boss, the route note someone posted at 3 a.m., the patch that invalidated a decade of practice and the community that rebuilt it by the weekend.</p><h2>The fandom inside the timer</h2><p>Categories, frame rules and emulated hardware arguments give each game a second rulebook written by its players. Discovery streams turn mechanical knowledge into spectacle, and the leaderboards double as a who-knows-what archive.</p><ul><li><strong>Any%</strong> — break the game hardest, finish soonest.</li><li><strong>100%</strong> — a negotiation with the developers\' original intent.</li><li><strong>Glitchless</strong> — respect for the intended path, executed without it.</li></ul><p>What looks like a solitary timer is one of the most collaborative structures in fandom: every saved frame was somebody\'s research.</p>'],

            ['title' => 'A Cosplay Judge\'s Notebook: What Actually Scores', 'cat' => 'cosplay', 'author' => 'lena@fanhubplus.test',
                'days' => 58, 'views' => 64000, 'featured' => false,
                'excerpt' => 'Craftsmanship scores reward decisions more than decoration — here is what the clipboard is really tracking.',
                'body' => '<p>The most common beginner mistake is treating the stage like a mirror: more rhinestones, more confidence. Judges are tracking something else entirely — evidence of choices under constraints.</p><h2>The four things on the sheet</h2><ul><li><strong>Construction</strong> — seams, edges and joins that will survive a second wear.</li><li><strong>Finish</strong> — paint and weathering that read as material, not as paint.</li><li><strong>Fit</strong> — movement tested against the walk, not the mirror.</li><li><strong>Accuracy</strong> — how the reference was interpreted, not merely copied.</li></ul><p>Interviews are where strong builds lose points. A builder who can explain why they chose EVA over thermoplastic, and what failed twice first, is demonstrating process — and process is what scores. Bring the prototype. Bring the mistake. The pristine version tells us the least.</p>'],

            ['title' => 'Comeback Season: How to Read a K-Pop Release Calendar', 'cat' => 'k-pop', 'author' => 'yuki@fanhubplus.test',
                'days' => 73, 'views' => 88000, 'featured' => false,
                'excerpt' => 'Teaser dates, concept photos, a title track drop — every comeback is a three-week story with a rhythm you can learn.',
                'body' => '<p>A comeback is not a release date; it is a campaign. Agencies choreograph it so that every artefact — schedule poster, concept photo, dance preview — lands at a moment designed to keep the fandom in a specific emotional register.</p><h2>The rhythm</h2><ul><li><strong>D-14</strong> — schedule poster. The fandom maps every icon like a treasure chart.</li><li><strong>D-10</strong> — concept photos begin. The visual thesis of the era is set here.</li><li><strong>D-3</strong> — highlight medley. First taste, full of deliberate misdirection.</li><li><strong>D-day</strong> — title track, performance video, then the broadcast circuit.</li></ul><p>Reading the calendar this way turns waiting into participation: voting windows, streaming projects and fan chant practice all slot into the same timeline. The comeback ends when the promotions do — but the era keeps going in fan projects and end-of-year stages.</p>'],

            ['title' => 'Panel Borders: How Manga Directs an Action Scene', 'cat' => 'manga', 'author' => 'priya@fanhubplus.test',
                'days' => 90, 'views' => 112000, 'featured' => false,
                'excerpt' => 'Before cameras and choreography, there was the gutter — and manga learned decades ago how to fight inside it.',
                'body' => '<p>A manga action scene is directed with page geometry. Panel shape sets tempo: tall narrow panels slow a moment to a heartbeat; wide horizontal ones carry speed. The gutter between them does the editing.</p><h2>Techniques worth noticing</h2><ul><li><strong>Broken borders</strong> — a limb crossing the frame announces impact before the sound effect does.</li><li><strong>Repeating panels</strong> — the same fist, three frames, an accumulation of force.</li><li><strong>Blank gutters</strong> — one silent beat where the reader supplies the noise.</li></ul><p>The best chapters let the eye land where the pain is. Read a favourite fight once without the dialogue and you will see the storyboard underneath — which is, of course, exactly what the anime adaptation had to translate.</p>'],

            ['title' => 'Every Frame a Poster: Stills That Outlived Their Films', 'cat' => 'movies', 'author' => 'sofia@fanhubplus.test',
                'days' => 116, 'views' => 76000, 'featured' => false,
                'excerpt' => 'Some shots keep circulating years after the credits — usually because they contain an entire argument in one image.',
                'body' => '<p>Fan artists rarely redraw entire scenes. They redraw a single composition: two silhouettes against a sunrise, a hallway shot, a close-up held one beat too long. The frames that survive are the ones already structured like posters — clear shapes, deliberate colour, room for a caption.</p><h2>Why these images travel</h2><p>They compress. A still carrying the film\'s whole thesis needs no spoiler tag, no runtime, no context — which is why it keeps appearing in profile pictures and convention badges years later.</p><ul><li>Strong negative space invites the fan edit.</li><li>Contrasting colour pairs survive cropping and small screens.</li><li>An ambiguous expression lets every fandom project onto it.</li></ul><p>The film may fade from rotation; the frame stays in the culture, quietly doing the marketing the studio stopped paying for.</p>'],

            ['title' => 'Long-Run Comics and the Art of the Soft Reboot', 'cat' => 'comics', 'author' => 'noah@fanhubplus.test',
                'days' => 138, 'views' => 58000, 'featured' => false,
                'excerpt' => 'Issue #1 is a promise; issue #401 is an archaeology dig. How long-running series reinvent themselves without breaking their own continuity.',
                'body' => '<p>Series that run for decades face a structural problem: new readers need an on-ramp, and old readers refuse to abandon their homework. The soft reboot is the compromise — a new creative team, a clean thematic starting point, and continuity kept in the filing cabinet rather than the script.</p><h2>What makes it work</h2><ul><li><strong>A status quo reset</strong> that changes relationships, not the premise.</li><li><strong>Trade-friendly arcs</strong> that resolve in four or six issues.</li><li><strong>Explicit #1 numbering</strong> for marketing, legacy numbering for pride.</li></ul><p>When it fails, readers feel their accumulated reading was rendered decorative. When it works, the book feels both familiar and newly dangerous — which is the only way anything survives past issue four hundred.</p>'],

            ['title' => 'Patch Notes as Serialised Storytelling', 'cat' => 'gaming', 'author' => 'marcus@fanhubplus.test',
                'days' => 160, 'views' => 104000, 'featured' => false,
                'excerpt' => 'Balance passes, reworked kits and a character nerfed into the ground — live-service games accidentally invented a serialised format.',
                'body' => '<p>Patch notes arrive on a schedule, feature named characters, escalate toward seasonal climaxes and generate genuine theories. That is a story — published as a changelog.</p><h2>The structure nobody planned</h2><p>Each cycle opens with a thesis (the meta problem), delivers adjustments as rising action, and ends with a community verdict that carries into the next cycle. Players do not passively receive it; they argue the canon before it ships, datamine the foreshadowing, and mourn the "death" of strategies the way fans mourn a write-off.</p><ul><li>A rework reframes a character\'s entire arc.</li><li>A long-awaited buff reads as redemption.</li><li>A quiet adjustment nobody noticed becomes a retcon three months later.</li></ul><p>Documentation teams never signed up to be showrunners, but the audience voted — every hotfix thread is now a recap episode.</p>'],

            ['title' => 'From Fanart to Freelance: Build the Portfolio First', 'cat' => 'cosplay', 'author' => 'lena@fanhubplus.test',
                'days' => 185, 'views' => 71000, 'featured' => false,
                'excerpt' => 'The gap between posting drawings and getting paid for them is mostly logistical — and it is crossable in public.',
                'body' => '<p>Talent is the assumption; the portfolio is the argument. Clients and editors are not looking for your best piece — they are looking for evidence that you can finish, iterate and hit a brief.</p><h2>What to post while you are still unpaid</h2><ul><li>Complete series over isolated masterpieces — four related pieces beat forty sketches.</li><li>One process thread per project — thumbnails, corrections, the version you nearly shipped.</li><li>A commission-friendly format sheet — sizes, turnaround, revision policy.</li></ul><p>Fan work is legitimate portfolio material: it proves you can work inside an existing visual language, which is most of what paid illustration turns out to be. Keep the references honest, credit the source material, and let the improvement be visible. The archive itself becomes the pitch.</p>'],
        ];

        foreach ($rows as $row) {
            $publishedAt = now()->subDays($row['days'])->startOfDay()->addHours(9);

            Article::query()->updateOrCreate(
                ['slug' => $this->uniqueSlug('articles', $row['title'])],
                [
                    'title' => $row['title'],
                    'category_id' => $categories[$row['cat']],
                    'author_id' => $authors[$row['author']],
                    'excerpt' => $row['excerpt'],
                    'body' => $row['body'],
                    'cover_image' => null,
                    'status' => Article::STATUS_PUBLISHED,
                    'reading_time' => max(1, (int) ceil(str_word_count(strip_tags($row['body'])) / 200)),
                    'views_count' => $row['views'],
                    'featured' => $row['featured'],
                    'published_at' => $publishedAt,
                ]
            );
        }

        // Volume rows via A1's factory (category + author pinned, no cascades;
        // featured left false so the curated pair stays the featured set).
        Article::factory()->count(2)->create([
            'category_id' => $categories->random(),
            'author_id' => $authors->random(),
            'featured' => false,
        ]);
    }
}
