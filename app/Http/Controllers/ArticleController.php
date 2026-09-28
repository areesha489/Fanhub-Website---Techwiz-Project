<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Support\BookmarkHelper;
use App\Support\Engagement;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    /**
     * Cards rendered per catalogue page.
     */
    private const PER_PAGE = 12;

    /**
     * Sort keys accepted by the listing.
     *
     * @var list<string>
     */
    private const SORTS = ['latest', 'popular', 'reading_time'];

    /**
     * Editorial listing: featured hero, category chips, search and sorting.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');
        $requestedSort = (string) $request->query('sort', 'latest');
        $sort = in_array($requestedSort, self::SORTS, true) ? $requestedSort : 'latest';

        $categories = collect();
        $articles = null;
        $hero = null;
        $activeCategory = null;
        $publishedCount = 0;
        $error = null;

        try {
            $categories = Category::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'accent']);

            $activeCategory = $categories->firstWhere('slug', $category);
            $publishedCount = Article::query()->published()->count();

            $hero = Article::query()
                ->published()
                ->with(['category', 'author'])
                ->where('featured', true)
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->first()
                ?? Article::query()
                    ->published()
                    ->with(['category', 'author'])
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->first();

            $query = Article::query()->published()->with(['category', 'author']);

            if ($search !== '') {
                $query->search($search);
            }

            if ($category !== '') {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $category));
            }

            if ($hero !== null) {
                $query->where('id', '!=', $hero->id);
            }

            match ($sort) {
                'popular' => $query->orderByDesc('views_count')->orderByDesc('id'),
                'reading_time' => $query->orderBy('reading_time')->orderByDesc('id'),
                default => $query->orderByDesc('published_at')->orderByDesc('id'),
            };

            $articles = $query->paginate(self::PER_PAGE)->withQueryString();
            $this->applyBookmarks($articles, 'article');

            if ($hero !== null) {
                $heroBookmarkId = BookmarkHelper::bookmarkIdFor('article', (int) $hero->id);
                $hero->bookmarked = $heroBookmarkId !== null;
                $hero->bookmark_id = $heroBookmarkId;
            }
        } catch (\Throwable $exception) {
            Log::warning('articles.index failed: '.$exception->getMessage());
            $hero = null;
            $error = 'We could not load the editorial archive right now. Please refresh the page or try again in a moment.';
        }

        return view('articles.index', [
            'articles' => $articles,
            'hero' => $hero,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'search' => $search,
            'category' => $category,
            'sort' => $sort,
            'publishedCount' => $publishedCount,
            'total' => $articles?->total() ?? 0,
            'error' => $error,
        ]);
    }

    /**
     * Single article with author card and related reading.
     */
    public function show(string $slug): View
    {
        try {
            $article = Article::query()
                ->published()
                ->with(['category', 'author'])
                ->where('slug', $slug)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            abort(404);
        } catch (\Throwable $exception) {
            Log::warning('articles.show failed: '.$exception->getMessage());

            return view('articles.show', [
                'article' => null,
                'loadError' => 'We could not load this article right now. Please try again in a moment.',
            ]);
        }

        Engagement::recordView($article, 'article');

        $related = collect();
        $isBookmarked = false;
        $bookmarkId = null;

        try {
            $related = Article::query()
                ->published()
                ->with(['category', 'author'])
                ->where('category_id', $article->category_id)
                ->where('id', '!=', $article->id)
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();

            if ($related->isEmpty()) {
                $related = Article::query()
                    ->published()
                    ->with(['category', 'author'])
                    ->where('id', '!=', $article->id)
                    ->orderByDesc('published_at')
                    ->limit(3)
                    ->get();
            }

            $bookmarkId = BookmarkHelper::bookmarkIdFor('article', (int) $article->id);
            $isBookmarked = $bookmarkId !== null;
        } catch (\Throwable $exception) {
            Log::warning('articles.show extras failed: '.$exception->getMessage());
        }

        return view('articles.show', [
            'article' => $article,
            'related' => $related,
            'isBookmarked' => $isBookmarked,
            'bookmarkId' => $bookmarkId,
            'loadError' => null,
        ]);
    }

    /**
     * Preload bookmark state for a page of cards in a single query.
     *
     * @param  LengthAwarePaginator<int, Article>  $paginator
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
