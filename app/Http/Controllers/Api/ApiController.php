<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Rating;
use App\Models\Release;
use App\Models\UserPreference;
use App\Support\ApiSerializer;
use App\Support\BookmarkHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Shared helpers for the JSON API controllers: envelopes, bookmarked maps
 * and safe per-page clamping.
 */
abstract class ApiController extends Controller
{
    /**
     * Single-resource envelope: {"data": ...}.
     */
    protected function data(mixed $payload): array
    {
        return ApiSerializer::single($payload);
    }

    /**
     * Map of model id => bookmark id for a collection (one query, guests get []).
     *
     * @param  iterable<mixed, Model>  $items
     * @return array<int, int>
     */
    protected function bookmarkedMap(string $type, iterable $items): array
    {
        $ids = [];

        foreach ($items as $item) {
            $ids[] = $item->getKey();
        }

        return BookmarkHelper::bookmarkedMap($type, $ids);
    }

    /**
     * Hydrate bookmarkable models in bulk (one query per type) and serialise.
     *
     * Rows whose underlying model is gone are dropped from the result.
     *
     * @param  iterable<mixed, Bookmark>  $bookmarks
     * @return list<array<string, mixed>>
     */
    protected function mapBookmarks(iterable $bookmarks): array
    {
        $rows = collect($bookmarks);

        $groups = [];

        foreach ($rows as $bookmark) {
            $groups[$bookmark->bookmarkable_type] ??= collect();
            $groups[$bookmark->bookmarkable_type]->push($bookmark);
        }

        foreach ($groups as $fqcn => $group) {
            $ids = $group->pluck('bookmarkable_id')->unique()->values()->all();

            $models = $fqcn::query()
                ->with(self::bookmarkEager($fqcn))
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($group as $bookmark) {
                $bookmark->setRelation('bookmarkable', $models->get($bookmark->bookmarkable_id));
            }
        }

        return $rows
            ->map(fn (Bookmark $bookmark): ?array => ApiSerializer::bookmarkItem($bookmark))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Relations worth eager loading per bookmarkable type.
     *
     * @return list<string>
     */
    private static function bookmarkEager(string $fqcn): array
    {
        return match ($fqcn) {
            Content::class => ['category', 'tags'],
            Article::class => ['category', 'author'],
            Character::class => ['category'],
            Media::class => ['category'],
            Event::class => ['category'],
            Merchandise::class => ['category'],
            Release::class => ['category'],
            default => [],
        };
    }

    /**
     * Pagination meta shared by hand-built list envelopes.
     */
    protected function paginatorMeta(LengthAwarePaginator $paginator): array
    {
        return ApiSerializer::paginatorMeta($paginator);
    }

    /**
     * Count of ratings given by a member (the User model has no relation).
     */
    protected function ratingCount(int $userId): int
    {
        return Rating::query()->where('user_id', $userId)->count();
    }

    /**
     * Public payload for a member's preference row.
     *
     * @return array<string, mixed>
     */
    protected function preferencePayload(UserPreference $preferences): array
    {
        return [
            'theme' => $preferences->theme,
            'font_scale' => (float) $preferences->font_scale,
            'reduced_motion' => (bool) $preferences->reduced_motion,
            'email_notifications' => (bool) $preferences->email_notifications,
            'content_notifications' => (bool) $preferences->content_notifications,
            'event_notifications' => (bool) $preferences->event_notifications,
            'newsletter' => (bool) $preferences->newsletter,
        ];
    }

    /**
     * Clamp the `per_page` query parameter to a sane range.
     */
    protected function perPage(Request $request, int $default = 12, int $max = 50): int
    {
        $value = (int) $request->input('per_page', $default);

        if ($value < 1) {
            return $default;
        }

        return min($max, $value);
    }

    /**
     * Resolve a category filter that may be a slug or a numeric id.
     */
    protected function categoryId(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        $id = Category::query()->where('slug', $value)->value('id');

        return $id === null ? null : (int) $id;
    }
}
