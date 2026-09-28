<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\ContentView;
use App\Models\Event;
use App\Models\Favorite;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Notification;
use App\Models\Rating;
use App\Models\Release;
use App\Models\User;
use App\Support\BookmarkHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class EngagementSeeder extends Seeder
{
    /**
     * Demo activity so every dashboard has something in it: bookmarks (with
     * real notes), category favourites, ratings, ~340 views spread over 60
     * days, notifications and activity log entries.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get()->values();
        $userCount = $users->count();

        if ($userCount === 0) {
            return;
        }

        $this->seedBookmarks($users);
        $this->seedFavorites($users);
        $this->seedRatings($users);
        $this->seedViews($users);
        $this->seedNotifications($users);
        $this->seedActivity($users, $userCount);
    }

    /**
     * One bookmark per target, rotated across members; every fourth gets a
     * real note (well over the five required).
     *
     * @param  Collection<int, User>  $users
     */
    private function seedBookmarks($users): void
    {
        $targets = [
            'content' => Content::query()->published()->orderBy('id')->limit(10)->pluck('id'),
            'character' => Character::query()->orderBy('id')->limit(8)->pluck('id'),
            'article' => Article::query()->published()->orderBy('id')->limit(6)->pluck('id'),
            'event' => Event::query()->orderBy('id')->limit(6)->pluck('id'),
            'media' => Media::query()->published()->orderBy('id')->limit(4)->pluck('id'),
            'release' => Release::query()->orderBy('id')->limit(4)->pluck('id'),
            'merchandise' => Merchandise::query()->published()->orderBy('id')->limit(4)->pluck('id'),
        ];

        $notes = [
            'Re-watch before the next season so the callbacks actually land.',
            'Reference for the cosplay build — the sleeve pattern is on the detail page.',
            'Keep checking: the date has slipped once already.',
            'Re-read after the finale; every hint is in chapter three.',
            'Sent to the group chat, everyone owes me an opinion.',
            'Restock window opens next month — look before buying anywhere.',
            'Judge notes mention this style, useful for the craftsmanship entry.',
            'Where the soundtrack podcast started — good timestamp for the OST talk.',
            'Prep for the panel; the interview answers the main question.',
            'Backup pick in case the first event sells out.',
            'Worth the revisit, the pacing holds up better than expected.',
            'Price seems high but the edition includes the liner notes.',
        ];

        $noteIndex = 0;
        $loggable = 0;

        foreach ($targets as $type => $ids) {
            $morph = BookmarkHelper::morphMap()[$type];

            foreach ($ids as $index => $id) {
                $user = $users[($index + $loggable) % $users->count()];
                $note = null;

                if ($index % 4 === 0) {
                    $note = $notes[$noteIndex % count($notes)];
                    $noteIndex++;
                }

                Bookmark::query()->firstOrCreate(
                    [
                        'user_id' => $user->getKey(),
                        'bookmarkable_type' => $morph,
                        'bookmarkable_id' => $id,
                    ],
                    ['note' => $note]
                );

                $loggable++;
            }
        }
    }

    /**
     * Category favourites matching each member's stated interests.
     *
     * @param  Collection<int, User>  $users
     */
    private function seedFavorites($users): void
    {
        $categories = Category::query()->pluck('id', 'slug');

        $interests = [
            'admin@fanhubplus.test' => ['anime', 'gaming'],
            'moderator@fanhubplus.test' => ['comics', 'cosplay'],
            'priya@fanhubplus.test' => ['manga', 'anime'],
            'marcus@fanhubplus.test' => ['gaming', 'movies'],
            'lena@fanhubplus.test' => ['cosplay', 'movies'],
            'yuki@fanhubplus.test' => ['k-pop', 'anime'],
            'noah@fanhubplus.test' => ['comics', 'tv-shows'],
            'sofia@fanhubplus.test' => ['movies', 'tv-shows'],
        ];

        foreach ($users as $user) {
            foreach ($interests[$user->email] ?? ['anime'] as $slug) {
                if (! isset($categories[$slug])) {
                    continue;
                }

                Favorite::query()->firstOrCreate([
                    'user_id' => $user->getKey(),
                    'favoritable_type' => Category::class,
                    'favoritable_id' => $categories[$slug],
                ]);
            }
        }
    }

    /**
     * 64 ratings (48 on titles, 16 on characters), values 6-10 on the 0-10 scale.
     *
     * @param  Collection<int, User>  $users
     */
    private function seedRatings($users): void
    {
        $contents = Content::query()->published()->orderBy('id')->limit(12)->pluck('id');
        $characters = Character::query()->orderBy('id')->limit(8)->pluck('id');

        foreach ($contents as $index => $id) {
            foreach ([0, 1, 2, 3] as $offset) {
                $user = $users[($index + $offset) % $users->count()];

                Rating::query()->firstOrCreate(
                    [
                        'user_id' => $user->getKey(),
                        'rateable_type' => Content::class,
                        'rateable_id' => $id,
                    ],
                    ['value' => 6 + (($index + $offset) % 5)]
                );
            }
        }

        foreach ($characters as $index => $id) {
            foreach ([0, 1] as $offset) {
                $user = $users[($index + $offset + 2) % $users->count()];

                Rating::query()->firstOrCreate(
                    [
                        'user_id' => $user->getKey(),
                        'rateable_type' => Character::class,
                        'rateable_id' => $id,
                    ],
                    ['value' => 7 + (($index + $offset) % 4)]
                );
            }
        }
    }

    /**
     * ~340 views scattered across the last 60 days (anonymous and signed-in).
     *
     * @param  Collection<int, User>  $users
     */
    private function seedViews($users): void
    {
        $targets = array_values(array_filter([
            [Content::class, Content::query()->published()->pluck('id')],
            [Character::class, Character::query()->pluck('id')],
            [Article::class, Article::query()->published()->pluck('id')],
            [Event::class, Event::query()->pluck('id')],
            [Media::class, Media::query()->published()->pluck('id')],
        ], fn (array $set): bool => $set[1]->isNotEmpty()));

        if ($targets === []) {
            return;
        }

        $rows = [];

        for ($i = 0; $i < 340; $i++) {
            [$class, $ids] = $targets[$i % count($targets)];

            $rows[] = [
                'user_id' => $i % 5 === 0 ? null : $users[$i % $users->count()]->getKey(),
                'viewable_type' => $class,
                'viewable_id' => $ids[($i * 3 + 1) % $ids->count()],
                'ip' => fake()->ipv4(),
                'created_at' => now()
                    ->subDays(fake()->numberBetween(0, 59))
                    ->subHours(fake()->numberBetween(0, 23))
                    ->format('Y-m-d H:i:s'),
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            ContentView::query()->insert($chunk);
        }
    }

    /**
     * A dozen-plus notifications, roughly half still unread.
     *
     * @param  Collection<int, User>  $users
     */
    private function seedNotifications($users): void
    {
        $items = [
            ['title' => 'Welcome to Fan Hub+',
                'body' => 'Your account is ready — pick a few favourite categories so the home page learns what you follow.',
                'link' => '/profile'],
            ['title' => 'Fresh in Anime',
                'body' => 'Two new articles landed in the Anime category this week, including a seasonal chart deep dive.',
                'link' => '/articles'],
            ['title' => 'Event reminder: Anime Frontier Expo 2026',
                'body' => 'The Tokyo expo opens in a few weeks — passes are still listed on the event page.',
                'link' => '/events'],
            ['title' => 'Release date confirmed',
                'body' => 'A title you bookmarked now has a confirmed date on the release calendar.',
                'link' => '/releases'],
            ['title' => 'Moderator replied to your feedback',
                'body' => 'Thanks for the report — the layout issue you flagged is fixed in the latest deploy.',
                'link' => '/dashboard'],
            ['title' => 'Bookmark note saved',
                'body' => 'Your note was attached — open Bookmarks to review everything you have saved.',
                'link' => '/bookmarks'],
            ['title' => 'New in the multimedia library',
                'body' => 'A trailer and a gallery were added to the library; the filter panel makes them easy to find.',
                'link' => '/media'],
            ['title' => 'Season pass: releases ahead',
                'body' => 'Three upcoming releases fall inside the next six months — check the calendar view.',
                'link' => '/releases'],
            ['title' => 'Community milestone',
                'body' => 'Your ratings helped a title cross into the trending list on the home page.',
                'link' => '/'],
            ['title' => 'Cosplay Craft Masters registrations open',
                'body' => 'The Paris workshop weekend is taking sign-ups — beginners can audit for free.',
                'link' => '/events'],
            ['title' => 'Preferences synced',
                'body' => 'Your theme and notification settings now apply on every device you sign in from.',
                'link' => '/settings'],
            ['title' => 'Draft preview available',
                'body' => 'An editor draft in the Anime section is waiting for a final pass before publishing.',
                'link' => '/articles'],
        ];

        foreach ($items as $index => $item) {
            $user = $users[$index % $users->count()];

            Notification::query()->firstOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'title' => $item['title'],
                ],
                [
                    'body' => $item['body'],
                    'link' => $item['link'],
                    'read_at' => $index % 2 === 0 ? now()->subDays(max(1, 12 - $index))->subHours(3) : null,
                ]
            );
        }
    }

    /**
     * 32 activity log rows covering every action except `submission`, which
     * FanSubmissionSeeder records against the real records.
     *
     * @param  Collection<int, User>  $users
     */
    private function seedActivity($users, int $userCount): void
    {
        $bookmarkedContent = Content::query()->published()->orderBy('id')->limit(6)->get(['id', 'title']);
        $ratedContent = Content::query()->published()->orderBy('id')->skip(4)->take(6)->get(['id', 'title']);

        $entries = [
            ['login', null, null, 'Signed in from a new browser.'],
            ['logout', null, null, 'Signed out after the session ended.'],
            ['bookmark_added', Content::class, null, null],
            ['rating', Content::class, null, null],
            ['profile_updated', null, null, 'Updated the display name and bio.'],
            ['bookmark_removed', Content::class, null, null],
            ['login', null, null, 'Signed in through the website.'],
            ['bookmark_added', Content::class, null, null],
            ['rating', Content::class, null, null],
            ['profile_updated', null, null, 'Changed notification preferences.'],
            ['logout', null, null, 'Signed out.'],
            ['bookmark_added', Content::class, null, null],
        ];

        for ($i = 0; $i < 32; $i++) {
            $entry = $entries[$i % count($entries)];
            $subjectId = null;
            $description = $entry[3];

            if ($entry[1] === Content::class) {
                if ($entry[0] === 'rating') {
                    $target = $ratedContent[$i % $ratedContent->count()];
                    $description = 'Left a rating on "'.$target->title.'".';
                } else {
                    $target = $bookmarkedContent[$i % $bookmarkedContent->count()];
                    $subjectId = $target->id;

                    $description = $entry[0] === 'bookmark_removed'
                        ? 'Removed "'.$target->title.'" from bookmarks.'
                        : 'Saved "'.$target->title.'" to bookmarks.';
                }
            }

            ActivityLog::query()->create([
                'user_id' => $users[$i % $userCount]->getKey(),
                'action' => $entry[0],
                'subject_type' => $entry[1] === Content::class && $entry[0] === 'rating' ? null : $entry[1],
                'subject_id' => $entry[0] === 'rating' ? null : $subjectId,
                'description' => $description,
                'ip_address' => fake()->ipv4(),
            ]);
        }
    }
}
