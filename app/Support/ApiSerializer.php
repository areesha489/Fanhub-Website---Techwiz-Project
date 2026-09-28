<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\FanSubmission;
use App\Models\Feedback;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Notification;
use App\Models\Release;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Canonical JSON serialisers for the /api endpoints.
 *
 * Every method returns a plain array that matches the field contract in the
 * master spec (§7). Image fields always come back as absolute URLs: stored
 * uploads are prefixed with asset(), empty values fall back to the generated
 * placeholder artwork via ph().
 *
 * `$bookmarked` maps are produced with BookmarkHelper::bookmarkedMap() so a
 * whole collection costs a single query (guests get an empty map => false).
 */
class ApiSerializer
{
    /*
    |--------------------------------------------------------------------------
    | Envelopes
    |--------------------------------------------------------------------------
    */

    /**
     * Envelope for a single resource: {"data": ...}.
     */
    public static function single(mixed $data): array
    {
        return ['data' => $data];
    }

    /**
     * Pagination meta for a paginator.
     */
    public static function paginatorMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    /**
     * Envelope for a paginator: {"data": [...], "meta": {...}}.
     *
     * @param  callable(Model): mixed  $mapper
     */
    public static function page(LengthAwarePaginator $paginator, callable $mapper): array
    {
        return [
            'data' => $paginator->getCollection()->map($mapper)->values()->all(),
            'meta' => self::paginatorMeta($paginator),
        ];
    }

    /**
     * Envelope for a plain (non-paginated) collection.
     *
     * @param  iterable<int, mixed>  $items
     * @param  callable(mixed): mixed  $mapper
     */
    public static function list(iterable $items, callable $mapper): array
    {
        $data = [];

        foreach ($items as $item) {
            $data[] = $mapper($item);
        }

        return [
            'data' => $data,
            'meta' => self::metaFor(count($data)),
        ];
    }

    /**
     * Pagination meta for a full (single page) result set.
     */
    public static function metaFor(int $total, ?int $perPage = null, int $currentPage = 1): array
    {
        return [
            'current_page' => $currentPage,
            'last_page' => 1,
            'per_page' => $perPage ?? $total,
            'total' => $total,
            'from' => $total > 0 ? 1 : null,
            'to' => $total > 0 ? $total : null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    */

    /**
     * Absolute URL for a stored image, falling back to the placeholder route.
     */
    public static function image(?string $value, string $label, int $width = 800, int $height = 450, ?string $tone = null): string
    {
        if ($value === null || trim($value) === '') {
            return url(ph($label, $width, $height, $tone));
        }

        $value = trim($value);

        if (preg_match('#^(https?:)?//#i', $value) === 1 || Str::startsWith($value, 'data:')) {
            return $value;
        }

        return asset($value);
    }

    /**
     * Map a stored gallery array to absolute URLs.
     *
     * @param  array<int, mixed>|null  $values
     * @return list<string>
     */
    public static function gallery(?array $values, string $label, ?string $tone = null, int $width = 800, int $height = 450): array
    {
        $urls = [];

        foreach ($values ?? [] as $value) {
            if (is_string($value) && trim($value) !== '') {
                $urls[] = self::image($value, $label, $width, $height, $tone);
            }
        }

        return $urls;
    }

    /**
     * Generated stills used when a model has no stored gallery.
     *
     * @return list<string>
     */
    public static function generatedGallery(string $label, ?string $tone = null, int $count = 3): array
    {
        $urls = [];

        for ($i = 1; $i <= $count; $i++) {
            $urls[] = url(ph($label.' Still '.$i, 800, 450, $tone));
        }

        return $urls;
    }

    /*
    |--------------------------------------------------------------------------
    | Shared fragments
    |--------------------------------------------------------------------------
    */

    /**
     * Embedded category reference: {id,name,slug,icon,accent}.
     */
    public static function categoryRef(?Category $category): ?array
    {
        if ($category === null) {
            return null;
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'icon' => $category->icon,
            'accent' => $category->accent,
        ];
    }

    /**
     * Category list/detail payload (adds description, image, position, counts).
     */
    public static function category(Category $category, int $contentCount = 0): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'icon' => $category->icon,
            'description' => $category->description,
            'image' => self::image($category->image, $category->name, 800, 450, $category->slug),
            'accent' => $category->accent,
            'position' => $category->position,
            'content_count' => $contentCount,
        ];
    }

    /**
     * Public user payload — never includes the password hash (hidden on the model).
     */
    public static function user(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => self::image($user->avatar, $user->name, 128, 128),
            'bio' => $user->bio,
            'role' => $user->role,
            'status' => $user->status,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * Admin user payload (adds soft-delete marker for the admin table).
     */
    public static function adminUser(User $user): array
    {
        return array_merge(self::user($user) ?? [], [
            'updated_at' => $user->updated_at?->toIso8601String(),
        ]);
    }

    public static function notification(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'body' => $notification->body,
            'link' => $notification->link,
            'read_at' => $notification->read_at?->toIso8601String(),
            'is_read' => $notification->read_at !== null,
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }

    public static function feedback(Feedback $feedback): array
    {
        return [
            'id' => $feedback->id,
            'user_id' => $feedback->user_id,
            'user' => self::user($feedback->relationLoaded('user') ? $feedback->user : null),
            'type' => $feedback->type,
            'subject' => $feedback->subject,
            'message' => $feedback->message,
            'status' => $feedback->status,
            'admin_response' => $feedback->admin_response,
            'created_at' => $feedback->created_at?->toIso8601String(),
            'updated_at' => $feedback->updated_at?->toIso8601String(),
        ];
    }

    public static function submission(FanSubmission $submission): array
    {
        return [
            'id' => $submission->id,
            'user_id' => $submission->user_id,
            'user' => self::user($submission->relationLoaded('user') ? $submission->user : null),
            'title' => $submission->title,
            'type' => $submission->type,
            'category' => self::categoryRef($submission->relationLoaded('category') ? $submission->category : null),
            'category_id' => $submission->category_id,
            'description' => $submission->description,
            'url' => $submission->url,
            'image' => $submission->image !== null ? self::image($submission->image, $submission->title, 800, 450) : null,
            'status' => $submission->status,
            'admin_note' => $submission->admin_note,
            'reviewed_at' => $submission->reviewed_at?->toIso8601String(),
            'created_at' => $submission->created_at?->toIso8601String(),
        ];
    }

    public static function activity(ActivityLog $activity): array
    {
        return [
            'id' => $activity->id,
            'action' => $activity->action,
            'description' => $activity->description,
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
            'created_at' => $activity->created_at?->toIso8601String(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Content
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<int, int>  $bookmarked
     */
    public static function content(Content $content, array $bookmarked = [], bool $detail = false): array
    {
        $tone = $content->category?->slug;
        $cover = self::image($content->cover_image, $content->title, 800, 1200, $tone);
        $banner = self::image($content->banner_image, $content->title, 1600, 720, $tone);

        $payload = [
            'id' => $content->id,
            'title' => $content->title,
            'slug' => $content->slug,
            'category_id' => $content->category_id,
            'category' => self::categoryRef($content->category),
            'content_type' => $content->content_type,
            'genre' => $content->genre,
            'description' => $content->description,
            'synopsis' => $content->synopsis,
            'cover_image' => $cover,
            'banner_image' => $banner,
            'release_date' => $content->release_date?->format('Y-m-d'),
            'status' => $content->status,
            'rating' => (float) $content->rating,
            'views_count' => (int) $content->views_count,
            'popularity' => (int) $content->popularity,
            'featured' => (bool) $content->featured,
            'trailer_url' => $content->trailer_url,
            'tags' => $content->tags->map(fn (Tag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->values()->all(),
            'bookmarked' => isset($bookmarked[$content->id]),
            'created_at' => $content->created_at?->toIso8601String(),
        ];

        if (! $detail) {
            return $payload;
        }

        $payload['characters'] = $content->characters
            ->map(fn (Character $character): array => self::character($character))
            ->values()
            ->all();

        $payload['media'] = $content->category_id !== null
            ? Media::query()
                ->published()
                ->where('category_id', $content->category_id)
                ->where('id', '!=', $content->id)
                ->with('category')
                ->latest('id')
                ->take(6)
                ->get()
                ->map(fn (Media $media): array => self::media($media))
                ->values()
                ->all()
            : [];

        $payload['related'] = Content::query()
            ->published()
            ->with('category', 'tags')
            ->where('id', '!=', $content->id)
            ->where(function ($query) use ($content): void {
                $query->where('category_id', $content->category_id)
                    ->orWhere('genre', $content->genre);
            })
            ->orderByDesc('views_count')
            ->take(6)
            ->get()
            ->map(fn (Content $related): array => self::content($related))
            ->values()
            ->all();

        $payload['gallery'] = array_values(array_filter([
            $cover,
            $banner,
            url(ph($content->title.' Still 1', 800, 450, $tone)),
            url(ph($content->title.' Still 2', 800, 450, $tone)),
        ]));

        return $payload;
    }

    /**
     * Compact hero slide derived from a featured title.
     */
    public static function heroSlide(Content $content): array
    {
        $tone = $content->category?->slug;

        return [
            'id' => $content->id,
            'title' => $content->title,
            'subtitle' => $content->genre,
            'description' => Str::limit(strip_tags((string) $content->description), 160),
            'category' => $content->category?->name,
            'image' => self::image($content->banner_image ?: $content->cover_image, $content->title, 1600, 720, $tone),
            'cta' => 'Explore Now',
            'link' => route('content.show', $content->slug),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Characters
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<int, int>  $bookmarked
     */
    public static function character(Character $character, array $bookmarked = [], bool $detail = false): array
    {
        $tone = $character->category?->slug;
        $image = self::image($character->image, $character->name, 600, 800, $tone);

        $payload = [
            'id' => $character->id,
            'name' => $character->name,
            'slug' => $character->slug,
            'category' => self::categoryRef($character->category),
            'category_id' => $character->category_id,
            'fandom' => $character->fandom,
            'image' => $image,
            'bio' => $character->bio,
            'gender' => $character->gender,
            'age' => $character->age,
            'affiliation' => $character->affiliation,
            'abilities' => $character->abilities,
            'debut' => $character->debut,
            'views_count' => (int) $character->views_count,
            'popularity' => (int) $character->popularity,
            'featured' => (bool) $character->featured,
            'bookmarked' => isset($bookmarked[$character->id]),
            'created_at' => $character->created_at?->toIso8601String(),
        ];

        if (! $detail) {
            return $payload;
        }

        $related = $character->relatedCharacters
            ->concat($character->relatedToCharacters)
            ->unique('id')
            ->values();

        $payload['gallery'] = self::gallery($character->gallery, $character->name, $tone) !== []
            ? self::gallery($character->gallery, $character->name, $tone)
            : self::generatedGallery($character->name, $tone);

        $payload['related_characters'] = $related->map(fn (Character $other): array => [
            'id' => $other->id,
            'name' => $other->name,
            'slug' => $other->slug,
            'image' => self::image($other->image, $other->name, 200, 200, $other->category?->slug),
            'relation' => $other->pivot?->relation ?? 'ally',
        ])->values()->all();

        $payload['contents'] = $character->contents
            ->filter(fn (Content $content): bool => $content->status === Content::STATUS_PUBLISHED)
            ->map(fn (Content $content): array => self::content($content))
            ->values()
            ->all();

        $payload['stats'] = [
            'views' => (int) $character->views_count,
            'bookmarks' => Bookmark::query()
                ->where('bookmarkable_type', Character::class)
                ->where('bookmarkable_id', $character->id)
                ->count(),
            'rating' => round((float) ($character->ratings()->avg('value') ?? 0), 1),
        ];

        return $payload;
    }

    /*
    |--------------------------------------------------------------------------
    | Articles
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<int, int>  $bookmarked
     */
    public static function article(Article $article, array $bookmarked = [], bool $detail = false): array
    {
        $tone = $article->category?->slug;

        $payload = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'category' => self::categoryRef($article->category),
            'author' => [
                'id' => $article->author?->id,
                'name' => $article->author?->name,
                'avatar' => self::image($article->author?->avatar, $article->author?->name ?? 'Fan Hub+', 96, 96),
            ],
            'excerpt' => $article->excerpt,
            'cover_image' => self::image($article->cover_image, $article->title, 800, 450, $tone),
            'status' => $article->status,
            'reading_time' => (int) $article->reading_time,
            'views_count' => (int) $article->views_count,
            'featured' => (bool) $article->featured,
            'published_at' => $article->published_at?->toIso8601String(),
            'created_at' => $article->created_at?->toIso8601String(),
            'bookmarked' => isset($bookmarked[$article->id]),
        ];

        if (! $detail) {
            return $payload;
        }

        $payload['body'] = $article->body;
        $payload['tags'] = self::articleTags($article);
        $payload['related'] = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->where('id', '!=', $article->id)
            ->where('category_id', $article->category_id)
            ->latest('published_at')
            ->take(4)
            ->get()
            ->map(fn (Article $related): array => self::article($related))
            ->values()
            ->all();

        return $payload;
    }

    /**
     * Articles have no tag pivot; derive labels from the category's tagged titles.
     *
     * @return list<array{id:int|null, name:string, slug:string}>
     */
    private static function articleTags(Article $article): array
    {
        $tags = Tag::query()
            ->whereHas('contents', fn ($query) => $query->where('category_id', $article->category_id))
            ->limit(8)
            ->get()
            ->map(fn (Tag $tag): array => ['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug])
            ->values()
            ->all();

        if ($tags === [] && $article->category !== null) {
            $tags[] = [
                'id' => null,
                'name' => $article->category->name,
                'slug' => $article->category->slug,
            ];
        }

        return $tags;
    }

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<int, int>  $bookmarked
     */
    public static function media(Media $media, array $bookmarked = [], bool $detail = false): array
    {
        $tone = $media->category?->slug;

        $payload = [
            'id' => $media->id,
            'title' => $media->title,
            'slug' => $media->slug,
            'category' => self::categoryRef($media->category),
            'media_type' => $media->media_type,
            'description' => $media->description,
            'thumbnail' => self::image($media->thumbnail, $media->title, 800, 450, $tone),
            'url' => $media->url,
            'embed_url' => Engagement::embedUrl($media->embed_url),
            'duration' => $media->duration,
            'gallery_images' => self::gallery($media->gallery, $media->title, $tone),
            'views_count' => (int) $media->views_count,
            'rating' => (float) $media->rating,
            'featured' => (bool) $media->featured,
            'status' => $media->status,
            'tags' => [
                ['id' => null, 'name' => Str::title($media->media_type), 'slug' => Str::slug($media->media_type)],
                ...($media->category !== null ? [[
                    'id' => $media->category->id,
                    'name' => $media->category->name,
                    'slug' => $media->category->slug,
                ]] : []),
            ],
            'bookmarked' => isset($bookmarked[$media->id]),
            'created_at' => $media->created_at?->toIso8601String(),
        ];

        if ($detail && $payload['gallery_images'] === []) {
            $payload['gallery_images'] = self::generatedGallery($media->title, $tone);
        }

        return $payload;
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<int, int>  $bookmarked
     */
    public static function event(Event $event, array $bookmarked = [], bool $detail = false): array
    {
        $tone = $event->category?->slug;

        $payload = [
            'id' => $event->id,
            'name' => $event->name,
            'slug' => $event->slug,
            'category' => self::categoryRef($event->category),
            'event_type' => $event->event_type,
            'description' => $event->description,
            'cover_image' => self::image($event->cover_image, $event->name, 800, 450, $tone),
            'start_date' => $event->start_date?->format('Y-m-d'),
            'end_date' => $event->end_date?->format('Y-m-d'),
            'time' => $event->time !== null ? substr((string) $event->time, 0, 5) : null,
            'venue' => $event->venue,
            'city' => $event->city,
            'country' => $event->country,
            'latitude' => $event->latitude !== null ? (float) $event->latitude : null,
            'longitude' => $event->longitude !== null ? (float) $event->longitude : null,
            'ticket_url' => $event->ticket_url,
            'status' => $event->status,
            'featured' => (bool) $event->featured,
            'views_count' => (int) $event->views_count,
            'bookmarked' => isset($bookmarked[$event->id]),
            'created_at' => $event->created_at?->toIso8601String(),
        ];

        if (! $detail) {
            return $payload;
        }

        $payload['related'] = Event::query()
            ->with('category')
            ->where('id', '!=', $event->id)
            ->where(function ($query) use ($event): void {
                $query->where('city', $event->city)
                    ->orWhere('category_id', $event->category_id);
            })
            ->orderBy('start_date')
            ->take(4)
            ->get()
            ->map(fn (Event $related): array => self::event($related))
            ->values()
            ->all();

        return $payload;
    }

    /*
    |--------------------------------------------------------------------------
    | Merchandise
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<int, int>  $bookmarked
     */
    public static function merchandise(Merchandise $merchandise, array $bookmarked = [], bool $detail = false): array
    {
        $tone = $merchandise->category?->slug;

        $payload = [
            'id' => $merchandise->id,
            'name' => $merchandise->name,
            'slug' => $merchandise->slug,
            'category' => self::categoryRef($merchandise->category),
            'fandom' => $merchandise->fandom,
            'description' => $merchandise->description,
            'image' => self::image($merchandise->image, $merchandise->title, 800, 800, $tone),
            'price' => $merchandise->price !== null ? (float) $merchandise->price : null,
            'currency' => $merchandise->currency,
            'tags' => array_values(array_filter($merchandise->tags ?? [], 'is_string')),
            'is_upcoming' => (bool) $merchandise->is_upcoming,
            'release_date' => $merchandise->release_date?->format('Y-m-d'),
            'popularity' => (int) $merchandise->popularity,
            'status' => $merchandise->status,
            'bookmarked' => isset($bookmarked[$merchandise->id]),
            'created_at' => $merchandise->created_at?->toIso8601String(),
        ];

        return $payload;
    }

    /*
    |--------------------------------------------------------------------------
    | Releases
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<int, int>  $bookmarked
     */
    public static function release(Release $release, array $bookmarked = []): array
    {
        $tone = $release->category?->slug;

        return [
            'id' => $release->id,
            'title' => $release->title,
            'slug' => $release->slug,
            'release_type' => $release->release_type,
            'category' => self::categoryRef($release->category),
            'release_date' => $release->release_date?->format('Y-m-d'),
            'description' => $release->description,
            'poster' => self::image($release->poster, $release->title, 600, 900, $tone),
            'status' => $release->status,
            'content_id' => $release->content_id,
            'popularity' => (int) $release->popularity,
            'bookmarked' => isset($bookmarked[$release->id]),
            'created_at' => $release->created_at?->toIso8601String(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Bookmarks
    |--------------------------------------------------------------------------
    */

    /**
     * Bookmark list meta item: {id,bookmarkable_type,bookmarkable_id,note,
     * created_at,title,subtitle,image,slug,url,type_label}.
     *
     * Returns null when the underlying model was hard-deleted.
     */
    public static function bookmarkItem(Bookmark $bookmark): ?array
    {
        $target = $bookmark->bookmarkable;

        if ($target === null) {
            return null;
        }

        $type = BookmarkHelper::typeFor($target);

        [$title, $subtitle, $image] = match ($type) {
            'content' => [$target->title, trim(($target->content_type ?? '').' · '.($target->genre ?? ''), ' ·'), $target->cover_image],
            'article' => [$target->title, $target->author?->name, $target->cover_image],
            'character' => [$target->name, $target->fandom, $target->image],
            'media' => [$target->title, Str::title((string) $target->media_type), $target->thumbnail],
            'event' => [$target->name, trim(($target->city ?? '').' · '.($target->venue ?? ''), ' ·'), $target->cover_image],
            'merchandise' => [$target->name, $target->fandom, $target->image],
            default => [$target->title, $target->release_date?->format('M Y'), $target->poster],
        };

        $tone = property_exists($target, 'category_id') ? ($target->category?->slug ?? null) : null;

        return [
            'id' => $bookmark->id,
            'bookmarkable_type' => $type,
            'bookmarkable_id' => $bookmark->bookmarkable_id,
            'note' => $bookmark->note,
            'created_at' => $bookmark->created_at?->toIso8601String(),
            'title' => (string) $title,
            'subtitle' => (string) $subtitle,
            'image' => self::image($image, (string) $title, 400, 300, $tone),
            'slug' => $target->slug ?? null,
            'url' => BookmarkHelper::urlFor($target),
            'type_label' => BookmarkHelper::typeLabel($type),
        ];
    }
}
