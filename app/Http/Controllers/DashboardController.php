<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\ContentView;
use App\Models\FanSubmission;
use App\Models\Favorite;
use App\Models\Media;
use App\Support\BookmarkHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Member home base.
 *
 * GET /dashboard
 */
class DashboardController extends Controller
{
    /**
     * Greeting, personal stats, favourites, activity and recommendations.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $userId = (int) $user->getKey();

        $bookmarkCounts = $this->bookmarkCounts($userId);
        $favoriteCategories = $this->favoriteCategories($userId);

        return view('dashboard', [
            'user' => $user,
            'stats' => [
                'bookmarks' => array_sum($bookmarkCounts),
                'articles' => $this->countByType($bookmarkCounts, Article::class),
                'views' => ContentView::query()->where('user_id', $userId)->count(),
                'submissions' => FanSubmission::query()->where('user_id', $userId)->count(),
            ],
            'favoriteCategories' => $favoriteCategories,
            'activities' => ActivityLog::query()
                ->where('user_id', $userId)
                ->latest('id')
                ->limit(8)
                ->get(),
            'recentBookmarks' => $this->projectedBookmarks($userId, 6),
            'recommended' => $this->recommended($favoriteCategories),
            'recentViews' => $this->recentlyViewed($userId, 6),
            'savedArticles' => $this->savedItems($userId, Article::class, 3),
            'savedCharacters' => $this->savedItems($userId, Character::class, 3),
            'savedMedia' => $this->savedItems($userId, Media::class, 3),
            'activityLabels' => self::activityLabels(),
        ]);
    }

    /**
     * Bookmark totals grouped by morph type.
     *
     * @return array<string, int> model class => count
     */
    private function bookmarkCounts(int $userId): array
    {
        $rows = Bookmark::query()
            ->where('user_id', $userId)
            ->selectRaw('bookmarkable_type as type, COUNT(*) as total')
            ->groupBy('bookmarkable_type')
            ->pluck('total', 'type');

        $counts = [];

        foreach ($rows as $type => $total) {
            $counts[(string) $type] = (int) $total;
        }

        return $counts;
    }

    /**
     * Count helper for the stat row.
     *
     * @param  array<string, int>  $counts
     */
    private function countByType(array $counts, string $class): int
    {
        return $counts[$class] ?? 0;
    }

    /**
     * Favourite fandoms (categories) of the member.
     *
     * @return Collection<int, Category>
     */
    private function favoriteCategories(int $userId): Collection
    {
        return Favorite::query()
            ->with('favoritable')
            ->where('user_id', $userId)
            ->where('favoritable_type', Category::class)
            ->get()
            ->pluck('favoritable')
            ->filter()
            ->values();
    }

    /**
     * Latest bookmarks projected to title/subtitle/image/url rows.
     *
     * @return array<int, array<string, mixed>>
     */
    private function projectedBookmarks(int $userId, int $limit): array
    {
        $bookmarks = Bookmark::query()
            ->with('bookmarkable')
            ->where('user_id', $userId)
            ->latest('id')
            ->limit($limit)
            ->get();

        return $bookmarks
            ->map(fn (Bookmark $bookmark): ?array => $this->projectBookmark($bookmark))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Row projection shared with the bookmarks page.
     *
     * @return array<string, mixed>|null
     */
    private function projectBookmark(Bookmark $bookmark): ?array
    {
        $model = $bookmark->bookmarkable;

        if ($model === null) {
            return [
                'id' => $bookmark->id,
                'title' => 'Removed item',
                'subtitle' => 'This entry is no longer available',
                'image' => null,
                'url' => null,
                'type_slug' => 'removed',
                'type_label' => 'Removed',
                'note' => $bookmark->note,
                'created_at' => $bookmark->created_at,
                'removed' => true,
            ];
        }

        $slug = (string) ($model->slug ?? $model->getKey());
        $typeSlug = BookmarkHelper::typeFor($model);

        return [
            'id' => $bookmark->id,
            'title' => (string) ($model->title ?? $model->name ?? 'Untitled'),
            'subtitle' => $this->subtitleFor($model),
            'image' => $model->cover_image ?? $model->image ?? $model->thumbnail ?? $model->poster ?? null,
            'url' => BookmarkHelper::urlFor($model),
            'type_slug' => $typeSlug,
            'type_label' => BookmarkHelper::typeLabel($typeSlug),
            'note' => $bookmark->note,
            'slug' => $slug,
            'created_at' => $bookmark->created_at,
            'removed' => false,
        ];
    }

    /**
     * Short secondary line for a bookmarked model.
     */
    private function subtitleFor(object $model): string
    {
        try {
            $category = $model->category;

            if ($category !== null && isset($category->name)) {
                return (string) $category->name;
            }
        } catch (\Throwable $exception) {
            // Models without a category relation simply fall through.
        }

        return (string) ($model->fandom ?? $model->genre ?? $model->content_type ?? '');
    }

    /**
     * Content for the favourites the member follows, otherwise the popular items.
     *
     * @param  Collection<int, Category>  $favoriteCategories
     * @return Collection<int, Content>
     */
    private function recommended(Collection $favoriteCategories): Collection
    {
        try {
            $query = Content::query()->with('category')->where('status', 'published');

            $categoryIds = $favoriteCategories
                ->pluck('id')
                ->filter()
                ->values();

            if ($categoryIds->isNotEmpty()) {
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->orderByDesc('popularity');
            }

            $items = $query->latest('id')->limit(4)->get();

            $bookmarked = BookmarkHelper::bookmarkedMap('content', $items->pluck('id')->all());
            $bookmarkIds = Bookmark::query()
                ->where('user_id', auth()->id())
                ->where('bookmarkable_type', Content::class)
                ->whereIn('bookmarkable_id', $items->pluck('id')->all())
                ->pluck('id', 'bookmarkable_id');

            foreach ($items as $item) {
                $item->setAttribute('bookmarked', isset($bookmarked[$item->id]));
                $item->setAttribute('bookmark_id', $bookmarkIds[$item->id] ?? null);
            }

            return $items;
        } catch (\Throwable $exception) {
            return collect();
        }
    }

    /**
     * Items the member opened recently.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentlyViewed(int $userId, int $limit): array
    {
        try {
            return ContentView::query()
                ->with('viewable')
                ->where('user_id', $userId)
                ->latest('id')
                ->limit($limit)
                ->get()
                ->map(function (ContentView $view): ?array {
                    $model = $view->viewable;

                    if ($model === null) {
                        return null;
                    }

                    $slug = (string) ($model->slug ?? $model->getKey());

                    return [
                        'title' => (string) ($model->title ?? $model->name ?? 'Untitled'),
                        'type_label' => BookmarkHelper::typeLabel(BookmarkHelper::typeFor($model)),
                        'image' => $model->cover_image ?? $model->image ?? $model->thumbnail ?? $model->poster ?? null,
                        'url' => BookmarkHelper::urlFor($model),
                        'viewed_at' => $view->created_at,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        } catch (\Throwable $exception) {
            return [];
        }
    }

    /**
     * Saved items of a single bookmarkable type.
     *
     * @return array<int, array<string, mixed>>
     */
    private function savedItems(int $userId, string $class, int $limit): array
    {
        return Bookmark::query()
            ->with('bookmarkable')
            ->where('user_id', $userId)
            ->where('bookmarkable_type', $class)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Bookmark $bookmark): ?array => $this->projectBookmark($bookmark))
            ->filter(fn (?array $row): bool => $row !== null && ! $row['removed'])
            ->values()
            ->all();
    }

    /**
     * Human readable labels for the activity timeline.
     *
     * @return array<string, string>
     */
    private static function activityLabels(): array
    {
        return [
            'login' => 'Signed in',
            'logout' => 'Signed out',
            'register' => 'Created an account',
            'profile_updated' => 'Updated the profile',
            'bookmark_added' => 'Saved an item',
            'bookmark_removed' => 'Removed a bookmark',
            'submission' => 'Sent a fan submission',
            'rating' => 'Rated an item',
            'feedback' => 'Sent feedback',
        ];
    }
}
