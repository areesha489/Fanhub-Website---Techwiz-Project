<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Support\BookmarkHelper;
use App\Support\Engagement;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Throwable;

class CharacterController extends Controller
{
    /**
     * Cards rendered per catalogue page.
     */
    private const PER_PAGE = 12;

    /**
     * Sort keys accepted by the catalogue.
     *
     * @var list<string>
     */
    private const SORTS = ['popular', 'latest', 'alphabetical'];

    /**
     * Character catalogue: category chips, free-text search and sorting.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');
        $requestedSort = (string) $request->query('sort', 'popular');
        $sort = in_array($requestedSort, self::SORTS, true) ? $requestedSort : 'popular';

        $categories = collect();
        $characters = null;
        $activeCategory = null;
        $error = null;

        try {
            $categories = Category::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'accent']);

            $activeCategory = $categories->firstWhere('slug', $category);

            $query = Character::query()->with('category');

            if ($search !== '') {
                $query->search($search);
            }

            if ($category !== '') {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $category));
            }

            match ($sort) {
                'latest' => $query->orderByDesc('created_at')->orderByDesc('id'),
                'alphabetical' => $query->orderBy('name'),
                default => $query->orderByDesc('popularity')->orderByDesc('views_count'),
            };

            $characters = $query->paginate(self::PER_PAGE)->withQueryString();
            $this->applyBookmarks($characters, 'character');
        } catch (Throwable $exception) {
            Log::warning('characters.index failed: '.$exception->getMessage());
            $error = 'We could not load the character catalogue right now. Please refresh the page or try again in a moment.';
        }

        return view('characters.index', [
            'characters' => $characters,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'search' => $search,
            'category' => $category,
            'sort' => $sort,
            'total' => $characters?->total() ?? 0,
            'error' => $error,
        ]);
    }

    /**
     * Character profile: portrait, biography, stats and related entries.
     */
    public function show(string $slug): View
    {
        try {
            $character = Character::query()
                ->with(['category'])
                ->where('slug', $slug)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            abort(404);
        } catch (Throwable $exception) {
            Log::warning('characters.show failed: '.$exception->getMessage());

            return view('characters.show', [
                'character' => null,
                'loadError' => 'We could not load this character profile right now. Please try again in a moment.',
            ]);
        }

        Engagement::recordView($character, 'character');

        $gallery = collect();
        $relatedCharacters = collect();
        $relatedContents = collect();
        $stats = ['views' => (int) $character->views_count, 'bookmarks' => 0, 'rating' => 0.0];
        $isBookmarked = false;
        $bookmarkId = null;

        try {
            $gallery = collect($character->gallery ?? [])->filter()->values();
            $stats['bookmarks'] = Bookmark::query()
                ->where('bookmarkable_type', Character::class)
                ->where('bookmarkable_id', $character->id)
                ->count();
            $stats['rating'] = (float) ($character->ratings()->avg('value') ?? 0);
            $bookmarkId = BookmarkHelper::bookmarkIdFor('character', (int) $character->id);
            $isBookmarked = $bookmarkId !== null;

            $relatedCharacters = $character->relatedCharacters()
                ->with(['category'])
                ->limit(6)
                ->get();

            if ($relatedCharacters->isEmpty()) {
                $relatedCharacters = $character->relatedToCharacters()
                    ->with(['category'])
                    ->limit(6)
                    ->get();
            }

            if ($relatedCharacters->isEmpty()) {
                $relatedCharacters = Character::query()
                    ->with(['category'])
                    ->where('category_id', $character->category_id)
                    ->where('id', '!=', $character->id)
                    ->orderByDesc('popularity')
                    ->limit(6)
                    ->get();
            }

            $relatedContents = $character->contents()
                ->with(['category'])
                ->limit(6)
                ->get();

            if ($relatedContents->isEmpty() && $character->category_id) {
                $relatedContents = Content::query()
                    ->with(['category'])
                    ->where('category_id', $character->category_id)
                    ->where('id', '!=', $character->id)
                    ->where('status', 'published')
                    ->orderByDesc('popularity')
                    ->limit(6)
                    ->get();
            }
        } catch (Throwable $exception) {
            Log::warning('characters.show extras failed: '.$exception->getMessage());
        }

        return view('characters.show', [
            'character' => $character,
            'gallery' => $gallery,
            'relatedCharacters' => $relatedCharacters,
            'relatedContents' => $relatedContents,
            'stats' => $stats,
            'isBookmarked' => $isBookmarked,
            'bookmarkId' => $bookmarkId,
            'loadError' => null,
        ]);
    }

    /**
     * Preload bookmark state for a page of cards in a single query.
     *
     * @param  LengthAwarePaginator<int, Character>  $paginator
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
