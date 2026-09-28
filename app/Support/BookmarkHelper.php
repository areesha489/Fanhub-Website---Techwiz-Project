<?php

namespace App\Support;

use App\Models\Bookmark;
use App\Models\BookmarkNote;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\Article;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Release;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Shared bookmark helpers used by web controllers, views and the JSON API.
 *
 * Bookmarks store the morph type as the model FQCN (no morph map is configured),
 * so lookups always go through morphMap() to convert the public type slug
 * (content, article, character, media, event, merchandise, release) to a class.
 */
class BookmarkHelper
{
    /**
     * Public type slug => Eloquent model class.
     *
     * @return array<string, class-string<Model>>
     */
    public static function morphMap(): array
    {
        return [
            'content' => Content::class,
            'article' => Article::class,
            'character' => Character::class,
            'media' => Media::class,
            'event' => Event::class,
            'merchandise' => Merchandise::class,
            'release' => Release::class,
        ];
    }

    /**
     * Reverse lookup: model class => public type slug.
     *
     * @return array<string, string>
     */
    public static function reverseMap(): array
    {
        return array_flip(self::morphMap());
    }

    /**
     * Whether the given type slug is a bookmarkable type.
     */
    public static function isValidType(string $type): bool
    {
        return array_key_exists($type, self::morphMap());
    }

    /**
     * Map of model id => bookmark id for a batch of ids (single query, no N+1).
     *
     * @param  array<int>  $ids
     * @return array<int, int>
     */
    public static function bookmarkedMap(string $type, array $ids): array
    {
        if (! Auth::check() || $ids === [] || ! self::isValidType($type)) {
            return [];
        }

        $morphClass = self::morphMap()[$type];

        return Bookmark::query()
            ->where('user_id', Auth::id())
            ->where('bookmarkable_type', $morphClass)
            ->whereIn('bookmarkable_id', $ids)
            ->pluck('id', 'bookmarkable_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * Bookmark id when the current user bookmarked the model, otherwise null.
     */
    public static function bookmarkIdFor(string $type, int $id): ?int
    {
        return self::bookmarkedMap($type, [$id])[$id] ?? null;
    }

    /**
     * Convenience boolean used by card components.
     */
    public static function isBookmarked(string $type, int $id): bool
    {
        return self::bookmarkIdFor($type, $id) !== null;
    }

    /**
     * Add or remove a bookmark for the authenticated user.
     *
     * @return array{bookmarked: bool, id: int|null, note: string|null}
     */
    public static function toggle(string $type, int $id, ?string $note = null): array
    {
        if (! Auth::check() || ! self::isValidType($type)) {
            return ['bookmarked' => false, 'id' => null, 'note' => null];
        }

        $morphClass = self::morphMap()[$type];
        $existing = Bookmark::query()
            ->where('user_id', Auth::id())
            ->where('bookmarkable_type', $morphClass)
            ->where('bookmarkable_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();

            return ['bookmarked' => false, 'id' => null, 'note' => null];
        }

        $bookmark = Bookmark::query()->create([
            'user_id' => Auth::id(),
            'bookmarkable_type' => $morphClass,
            'bookmarkable_id' => $id,
            'note' => $note,
        ]);

        if ($note) {
            BookmarkNote::query()->create(['bookmark_id' => $bookmark->id, 'note' => $note]);
        }

        return ['bookmarked' => true, 'id' => $bookmark->id, 'note' => $bookmark->note];
    }

    /**
     * Update the note on a bookmark and keep the note history.
     */
    public static function updateNote(Bookmark $bookmark, ?string $note): void
    {
        $bookmark->note = $note;
        $bookmark->save();

        if ($note !== null && $note !== '') {
            BookmarkNote::query()->create(['bookmark_id' => $bookmark->id, 'note' => $note]);
        }
    }

    /**
     * Public type slug for a given model instance (falls back to 'content').
     */
    public static function typeFor(Model $model): string
    {
        return self::reverseMap()[$model::class] ?? 'content';
    }

    /**
     * Human label for a type slug.
     */
    public static function typeLabel(string $type): string
    {
        $labels = [
            'content' => 'Content',
            'article' => 'Article',
            'character' => 'Character',
            'media' => 'Media',
            'event' => 'Event',
            'merchandise' => 'Merchandise',
            'release' => 'Release',
        ];

        return $labels[$type] ?? ucfirst($type);
    }

    /**
     * Website route for a bookmarked model, used by the bookmarks page.
     */
    public static function urlFor(Model $model): string
    {
        $type = self::typeFor($model);

        $route = match ($type) {
            'content' => 'content.show',
            'article' => 'articles.show',
            'character' => 'characters.show',
            'media' => 'media.show',
            'event' => 'events.show',
            'merchandise' => 'merchandise.show',
            default => 'releases.index',
        };

        return route($route, $model->getRouteKey());
    }
}
