<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\BookmarkHelper;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Throwable;

/**
 * One reusable category landing page for every fandom slug.
 */
class CategoryController extends Controller
{
    /**
     * Number of category contents per page.
     */
    private const PER_PAGE = 12;

    /**
     * Sort orders accepted on a category page.
     *
     * @var list<string>
     */
    private const SORTS = ['latest', 'popular', 'rating', 'alphabetical'];

    /**
     * GET /category/{slug}
     */
    public function show(Request $request, string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        $filters = [
            'type' => trim((string) $request->query('type', '')),
            'genre' => trim((string) $request->query('genre', '')),
            'year' => trim((string) $request->query('year', '')),
            'sort' => in_array((string) $request->query('sort'), self::SORTS, true)
                ? (string) $request->query('sort')
                : 'latest',
        ];

        $contents = null;
        $error = null;
        $stats = [
            'contents' => 0,
            'characters' => 0,
            'articles' => 0,
            'media' => 0,
            'releases' => 0,
        ];
        $featured = collect();
        $trending = collect();
        $characters = collect();
        $articles = collect();
        $multimedia = collect();
        $releases = collect();
        $genres = collect();
        $years = collect();
        $types = collect();

        try {
            $stats = [
                'contents' => $category->contents()->published()->count(),
                'characters' => $category->characters()->count(),
                'articles' => $category->articles()->published()->count(),
                'media' => $category->media()->published()->count(),
                'releases' => $category->releases()->count(),
            ];

            $genres = $category->contents()->published()->whereNotNull('genre')->distinct()->orderBy('genre')->pluck('genre');
            $years = $category->contents()
                ->published()
                ->whereNotNull('release_date')
                ->pluck('release_date')
                ->map(fn ($date): int => $date instanceof CarbonInterface
                    ? $date->year
                    : (int) substr((string) $date, 0, 4))
                ->unique()
                ->sortDesc()
                ->values();
            $types = $category->contents()->published()->distinct()->orderBy('content_type')->pluck('content_type');

            $contents = $this->filteredContents($category, $filters)->paginate(self::PER_PAGE)->withQueryString();

            $featured = $this->withBookmarks('content', $category->contents()
                ->published()
                ->with('category')
                ->where('featured', true)
                ->orderByDesc('popularity')
                ->take(6)
                ->get());

            $trending = $this->withBookmarks('content', $category->contents()
                ->published()
                ->with('category')
                ->orderByDesc('popularity')
                ->orderByDesc('views_count')
                ->take(6)
                ->get());

            $characters = $this->withBookmarks('character', $category->characters()
                ->with('category')
                ->orderByDesc('featured')
                ->orderByDesc('popularity')
                ->take(8)
                ->get());

            $articles = $this->withBookmarks('article', $category->articles()
                ->published()
                ->with(['category', 'author'])
                ->orderByDesc('published_at')
                ->take(6)
                ->get());

            $multimedia = $this->withBookmarks('media', $category->media()
                ->published()
                ->with('category')
                ->orderByDesc('views_count')
                ->take(6)
                ->get());

            $releases = $this->withBookmarks('release', $category->releases()
                ->with(['category', 'content'])
                ->orderBy('release_date')
                ->take(6)
                ->get());
        } catch (Throwable $exception) {
            report($exception);
            $error = 'This category could not be loaded right now. Please try again.';
        }

        return view('category', [
            'category' => $category,
            'filters' => $filters,
            'contents' => $contents,
            'stats' => $stats,
            'featured' => $featured,
            'trending' => $trending,
            'characters' => $characters,
            'articles' => $articles,
            'multimedia' => $multimedia,
            'releases' => $releases,
            'genres' => $genres,
            'years' => $years,
            'types' => $types,
            'error' => $error,
        ]);
    }

    /**
     * Category contents with the active type/genre/year/sort filters applied.
     *
     * @param  array<string, string>  $filters
     */
    private function filteredContents(Category $category, array $filters)
    {
        $query = $category->contents()->published()->with('category');

        if ($filters['type'] !== '') {
            $query->where('content_type', $filters['type']);
        }

        if ($filters['genre'] !== '') {
            $query->where('genre', $filters['genre']);
        }

        if ($filters['year'] !== '' && ctype_digit($filters['year'])) {
            $query->whereYear('release_date', (int) $filters['year']);
        }

        match ($filters['sort']) {
            'popular' => $query->orderByDesc('popularity')->orderByDesc('views_count'),
            'rating' => $query->orderByDesc('rating')->orderByDesc('popularity'),
            'alphabetical' => $query->orderBy('title'),
            default => $query->orderByDesc('release_date')->orderByDesc('id'),
        };

        return $query;
    }

    /**
     * Batch-load bookmark state for a collection (one query, no N+1).
     *
     * @param  Collection<int, Model>  $items
     * @return Collection<int, Model>
     */
    private function withBookmarks(string $type, Collection $items): Collection
    {
        if (! auth()->check() || $items->isEmpty()) {
            return $items;
        }

        $map = BookmarkHelper::bookmarkedMap($type, $items->pluck('id')->all());

        foreach ($items as $item) {
            $bookmarkId = $map[(int) $item->getKey()] ?? null;
            $item->setAttribute('bookmarked', $bookmarkId !== null);
            $item->setAttribute('bookmark_id', $bookmarkId);
        }

        return $items;
    }
}
