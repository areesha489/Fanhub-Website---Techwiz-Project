<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Support\BookmarkHelper;
use App\Support\Engagement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Saved items: listing with filters, AJAX toggle, note editing, removal.
 *
 * GET|POST /bookmarks · PUT|DELETE /bookmarks/{bookmark}
 */
class BookmarkController extends Controller
{
    /**
     * Per-page size of the bookmark grid.
     */
    private const PER_PAGE = 20;

    /**
     * Filterable list of everything the member saved.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $types = BookmarkHelper::morphMap();

        $activeType = (string) $request->query('type', 'all');
        if ($activeType !== 'all' && ! array_key_exists($activeType, $types)) {
            $activeType = 'all';
        }

        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort') === 'oldest' ? 'oldest' : 'recent';

        $counts = $this->typeCounts($user->getKey());

        $query = Bookmark::query()
            ->with(['bookmarkable', 'bookmarkable.category'])
            ->where('user_id', $user->getKey());

        if ($activeType !== 'all') {
            $query->where('bookmarkable_type', $types[$activeType]);
        }

        if ($search !== '') {
            $this->applySearch($query, $search, $types);
        }

        $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');

        /** @var LengthAwarePaginator<int, array<string, mixed>> $bookmarks */
        $bookmarks = $query->paginate(self::PER_PAGE)->withQueryString();
        $bookmarks->setCollection(
            $bookmarks->getCollection()
                ->map(fn (Bookmark $bookmark): array => $this->project($bookmark))
        );

        return view('bookmarks', [
            'bookmarks' => $bookmarks,
            'counts' => $counts,
            'types' => $types,
            'activeType' => $activeType,
            'search' => $search,
            'sort' => $sort,
            'total' => array_sum($counts),
        ]);
    }

    /**
     * Save an item. JSON posts (the x-bookmark button) receive JSON,
     * plain form posts are redirected back with a toast.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $types = BookmarkHelper::morphMap();

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys($types))],
            'id' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $type = $validated['type'];
        $modelId = (int) $validated['id'];
        $class = $types[$type];

        if (! $class::query()->whereKey($modelId)->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'That item no longer exists.',
                ], 422);
            }

            return back()->with('error', 'That item is no longer available.');
        }

        $existing = Bookmark::query()
            ->where('user_id', $request->user()->getKey())
            ->where('bookmarkable_type', $class)
            ->where('bookmarkable_id', $modelId)
            ->first();

        if ($request->expectsJson()) {
            if ($existing !== null) {
                return response()->json([
                    'bookmarked' => true,
                    'id' => $existing->id,
                    'note' => $existing->note,
                ], 200);
            }

            $bookmark = Bookmark::query()->create([
                'user_id' => $request->user()->getKey(),
                'bookmarkable_type' => $class,
                'bookmarkable_id' => $modelId,
                'note' => $validated['note'] ?? null,
            ]);

            Engagement::log(
                $request->user()->getKey(),
                'bookmark_added',
                'Saved “'.$this->titleOf($class, $modelId).'” to bookmarks',
                $class,
                $modelId
            );

            return response()->json([
                'bookmarked' => true,
                'id' => $bookmark->id,
                'note' => $bookmark->note,
            ], 201);
        }

        $result = BookmarkHelper::toggle($type, $modelId, $validated['note'] ?? null);

        return back()->with(
            $result['bookmarked'] ? 'success' : 'info',
            $result['bookmarked'] ? 'Saved to your bookmarks.' : 'Removed from your bookmarks.'
        );
    }

    /**
     * Update the personal note attached to a bookmark.
     */
    public function update(Request $request, Bookmark $bookmark): JsonResponse|RedirectResponse
    {
        $this->authorizeBookmark($request, $bookmark);

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        BookmarkHelper::updateNote($bookmark, $validated['note'] ?? null);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'note' => $bookmark->fresh()?->note,
            ]);
        }

        return back()->with('success', 'Your note has been saved.');
    }

    /**
     * Remove a bookmark (AJAX or classic form post).
     */
    public function destroy(Request $request, Bookmark $bookmark): JsonResponse|RedirectResponse
    {
        $this->authorizeBookmark($request, $bookmark);

        $title = $this->titleFor($bookmark);
        $modelId = (int) $bookmark->bookmarkable_id;
        $class = (string) $bookmark->bookmarkable_type;

        $bookmark->delete();

        Engagement::log(
            $request->user()->getKey(),
            'bookmark_removed',
            'Removed “'.$title.'” from bookmarks',
            $class,
            $modelId
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'bookmarked' => false,
                'id' => null,
            ]);
        }

        return back()->with('info', 'Removed “'.$title.'” from your bookmarks.');
    }

    /**
     * Only the owner may read or change a bookmark row.
     */
    private function authorizeBookmark(Request $request, Bookmark $bookmark): void
    {
        $userId = (int) $request->user()?->getKey();

        abort_unless((int) $bookmark->user_id === $userId && $userId !== 0, 403);
    }

    /**
     * Bookmark totals per type slug for the filter chips.
     *
     * @return array<string, int>
     */
    private function typeCounts(int $userId): array
    {
        $rows = Bookmark::query()
            ->where('user_id', $userId)
            ->selectRaw('bookmarkable_type as type, COUNT(*) as total')
            ->groupBy('bookmarkable_type')
            ->pluck('total', 'type');

        $reverse = BookmarkHelper::reverseMap();
        $counts = ['all' => 0];

        foreach ($rows as $class => $total) {
            $slug = $reverse[(string) $class] ?? null;

            if ($slug === null) {
                continue;
            }

            $counts[$slug] = (int) $total;
            $counts['all'] += (int) $total;
        }

        return $counts;
    }

    /**
     * Filter on the note text or the title/name of the bookmarked model.
     *
     * @param  Builder<Bookmark>  $query
     * @param  array<string, class-string>  $types
     */
    private function applySearch(Builder $query, string $search, array $types): void
    {
        $like = '%'.$search.'%';
        $matches = [];

        foreach ($types as $slug => $class) {
            $column = in_array($slug, ['character', 'event'], true) ? 'name' : 'title';

            try {
                $ids = $class::query()
                    ->where($column, 'like', $like)
                    ->limit(1000)
                    ->pluck('id');
            } catch (\Throwable $exception) {
                $ids = collect();
            }

            if ($ids->isNotEmpty()) {
                $matches[$class] = $ids;
            }
        }

        $query->where(function ($outer) use ($like, $matches) {
            $outer->where('note', 'like', $like);

            foreach ($matches as $class => $ids) {
                $outer->orWhere(function ($inner) use ($class, $ids) {
                    $inner->where('bookmarkable_type', $class)
                        ->whereIn('bookmarkable_id', $ids);
                });
            }
        });
    }

    /**
     * Denormalised row used by the bookmarks page.
     *
     * @return array<string, mixed>
     */
    private function project(Bookmark $bookmark): array
    {
        $model = $bookmark->bookmarkable;

        if ($model === null) {
            return [
                'id' => $bookmark->id,
                'bookmarkable_type' => $bookmark->bookmarkable_type,
                'bookmarkable_id' => $bookmark->bookmarkable_id,
                'note' => $bookmark->note,
                'title' => 'Removed item',
                'subtitle' => 'This entry has since been deleted',
                'image' => null,
                'slug' => null,
                'url' => null,
                'type_label' => 'Removed',
                'type_slug' => 'removed',
                'created_at' => $bookmark->created_at,
                'missing' => true,
            ];
        }

        $typeSlug = BookmarkHelper::typeFor($model);

        return [
            'id' => $bookmark->id,
            'bookmarkable_type' => $bookmark->bookmarkable_type,
            'bookmarkable_id' => $bookmark->bookmarkable_id,
            'note' => $bookmark->note,
            'title' => (string) ($model->title ?? $model->name ?? 'Untitled'),
            'subtitle' => $this->subtitleFor($model),
            'image' => $model->cover_image ?? $model->image ?? $model->thumbnail ?? $model->poster ?? null,
            'slug' => $model->slug ?? null,
            'url' => BookmarkHelper::urlFor($model),
            'type_label' => BookmarkHelper::typeLabel($typeSlug),
            'type_slug' => $typeSlug,
            'created_at' => $bookmark->created_at,
            'missing' => false,
        ];
    }

    /**
     * Category / fandom line under the bookmarked title.
     */
    private function subtitleFor(object $model): string
    {
        try {
            $category = $model->category;

            if ($category !== null && isset($category->name)) {
                return (string) $category->name;
            }
        } catch (\Throwable $exception) {
            // No category relation on this model — fall through to the next hint.
        }

        return (string) ($model->fandom ?? $model->genre ?? $model->content_type ?? '');
    }

    /**
     * Title of a bookmark's target model when it still exists.
     */
    private function titleFor(Bookmark $bookmark): string
    {
        $model = $bookmark->bookmarkable;

        if ($model === null) {
            return 'this item';
        }

        return (string) ($model->title ?? $model->name ?? 'this item');
    }

    /**
     * Fetch a title by class + id (used when creating a bookmark).
     */
    private function titleOf(string $class, int $modelId): string
    {
        try {
            $model = $class::query()->find($modelId);

            if ($model !== null) {
                return (string) ($model->title ?? $model->name ?? 'an item');
            }
        } catch (\Throwable $exception) {
            // Fall through to the generic label.
        }

        return 'an item';
    }
}
