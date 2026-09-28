<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Merchandise;
use App\Support\BookmarkHelper;
use App\Support\Engagement;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class MerchandiseController extends Controller
{
    /**
     * Cards rendered per showcase page.
     */
    private const PER_PAGE = 12;

    /**
     * Sort keys accepted by the showcase.
     *
     * @var list<string>
     */
    private const SORTS = ['popular', 'price_asc', 'price_desc', 'latest'];

    /**
     * Showcase listing — display only, no basket or payment flow anywhere.
     */
    public function index(Request $request): View
    {
        $fandom = (string) $request->query('fandom', '');
        $category = (string) $request->query('category', '');
        $tag = (string) $request->query('tag', '');
        $upcoming = (string) $request->query('upcoming', '');
        $requestedSort = (string) $request->query('sort', 'popular');
        $sort = in_array($requestedSort, self::SORTS, true) ? $requestedSort : 'popular';

        $categories = collect();
        $fandoms = collect();
        $tags = collect();
        $items = null;
        $activeCategory = null;
        $error = null;

        try {
            $categories = Category::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'accent']);

            $activeCategory = $categories->firstWhere('slug', $category);

            $fandoms = Merchandise::query()
                ->published()
                ->whereNotNull('fandom')
                ->where('fandom', '!=', '')
                ->distinct()
                ->orderBy('fandom')
                ->pluck('fandom');

            $tags = Merchandise::query()
                ->published()
                ->pluck('tags')
                ->flatMap(fn ($value) => is_array($value) ? $value : [])
                ->filter()
                ->unique(fn ($value) => mb_strtolower((string) $value))
                ->sort()
                ->values();

            $query = Merchandise::query()->published()->with('category');

            if ($fandom !== '') {
                $query->where('fandom', $fandom);
            }

            if ($category !== '') {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $category));
            }

            if ($tag !== '') {
                $query->whereJsonContains('tags', $tag);
            }

            if ($upcoming === '1') {
                $query->where('is_upcoming', true);
            } elseif ($upcoming === '0') {
                $query->where('is_upcoming', false);
            }

            match ($sort) {
                'price_asc' => $query->orderByRaw('price IS NULL, price ASC'),
                'price_desc' => $query->orderByRaw('price IS NULL, price DESC'),
                'latest' => $query->orderByDesc('created_at')->orderByDesc('id'),
                default => $query->orderByDesc('popularity')->orderByDesc('id'),
            };

            $items = $query->paginate(self::PER_PAGE)->withQueryString();
            $this->applyBookmarks($items, 'merchandise');
        } catch (\Throwable $exception) {
            Log::warning('merchandise.index failed: '.$exception->getMessage());
            $error = 'We could not load the showcase right now. Please refresh the page or try again in a moment.';
        }

        return view('merchandise.index', [
            'items' => $items,
            'categories' => $categories,
            'fandoms' => $fandoms,
            'tags' => $tags,
            'activeCategory' => $activeCategory,
            'fandom' => $fandom,
            'category' => $category,
            'tag' => $tag,
            'upcoming' => $upcoming,
            'sort' => $sort,
            'total' => $items?->total() ?? 0,
            'error' => $error,
        ]);
    }

    /**
     * Showcase detail — product information only, Fan Hub+ never processes payment.
     */
    public function show(string $slug): View
    {
        try {
            $item = Merchandise::query()
                ->with(['category'])
                ->where('slug', $slug)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            abort(404);
        } catch (\Throwable $exception) {
            Log::warning('merchandise.show failed: '.$exception->getMessage());

            return view('merchandise.show', [
                'item' => null,
                'loadError' => 'We could not load this collectible right now. Please try again in a moment.',
            ]);
        }

        Engagement::recordView($item, 'merchandise');

        $related = collect();
        $isBookmarked = false;
        $bookmarkId = null;

        try {
            $related = Merchandise::query()
                ->published()
                ->with(['category'])
                ->where('id', '!=', $item->id)
                ->where(function ($relatedQuery) use ($item): void {
                    $relatedQuery->where('fandom', $item->fandom)
                        ->orWhere('category_id', $item->category_id);
                })
                ->orderByDesc('popularity')
                ->limit(4)
                ->get();

            $bookmarkId = BookmarkHelper::bookmarkIdFor('merchandise', (int) $item->id);
            $isBookmarked = $bookmarkId !== null;
        } catch (\Throwable $exception) {
            Log::warning('merchandise.show extras failed: '.$exception->getMessage());
        }

        return view('merchandise.show', [
            'item' => $item,
            'related' => $related,
            'isBookmarked' => $isBookmarked,
            'bookmarkId' => $bookmarkId,
            'loadError' => null,
        ]);
    }

    /**
     * Preload bookmark state for a page of cards in a single query.
     *
     * @param  LengthAwarePaginator<int, Merchandise>  $paginator
     */
    private function applyBookmarks(LengthAwarePaginator $paginator, string $type): void
    {
        $map = BookmarkHelper::bookmarkedMap(
            $type,
            $paginator->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all()
        );

        foreach ($paginator->getCollection() as $item) {
            $item->bookmarked = array_key_exists((int) $item->id, $map);
            $item->bookmark_id = $map[(int) $item->id] ?? null;
        }
    }
}
