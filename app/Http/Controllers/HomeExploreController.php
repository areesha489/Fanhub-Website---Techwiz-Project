<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\FanSubmission;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Rating;
use App\Models\Release;
use App\Support\BookmarkHelper;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

/**
 * Public landing pages: the dashboard homepage and the filtered catalogue.
 */
class HomeExploreController extends Controller
{
    /**
     * Number of results per page on /explore.
     */
    private const PER_PAGE = 12;

    /**
     * Sort orders accepted by /explore.
     *
     * @var list<string>
     */
    private const SORTS = ['latest', 'popular', 'rating', 'alphabetical'];

    /**
     * GET / — data-driven dashboard homepage.
     */
    public function home(): View
    {
        $heroSlides = collect();
        $categories = collect();
        $trending = collect();
        $featuredCharacters = collect();
        $latestArticles = collect();
        $multimedia = collect();
        $upcomingEvents = collect();
        $upcomingReleases = collect();
        $communityPosts = collect();
        $merchandise = collect();
        $railProfile = null;
        $homeError = null;

        try {
            $heroSlides = $this->heroSlides();
            $categories = Category::query()
                ->whereIn('slug', ['anime', 'gaming', 'movies', 'tv-shows', 'k-pop', 'comics', 'manga', 'cosplay'])
                ->orderBy('position')
                ->orderBy('name')
                ->withCount(['contents as content_count' => fn ($query) => $query->published()])
                ->get();

            $trending = $this->withBookmarks('content', Content::query()
                ->published()
                ->with('category')
                ->orderByDesc('popularity')
                ->orderByDesc('views_count')
                ->take(10)
                ->get());

            $featuredCharacters = $this->withBookmarks('character', Character::query()
                ->with('category')
                ->orderByDesc('featured')
                ->orderByDesc('popularity')
                ->take(8)
                ->get());

            $latestArticles = $this->withBookmarks('article', Article::query()
                ->published()
                ->with(['category', 'author'])
                ->orderByDesc('published_at')
                ->take(6)
                ->get());

            $multimedia = $this->mixedMedia();

            $upcomingEvents = $this->withBookmarks('event', Event::query()
                ->with('category')
                ->whereIn('status', ['upcoming', 'ongoing'])
                ->orderBy('start_date')
                ->take(4)
                ->get());

            $communityPosts = FanSubmission::query()
                ->approved()
                ->with(['user:id,name', 'category:id,name,slug'])
                ->orderByDesc('reviewed_at')
                ->orderByDesc('id')
                ->take(4)
                ->get();

            $upcomingReleases = $this->withBookmarks('release', Release::query()
                ->with(['category', 'content'])
                ->upcoming()
                ->orderBy('release_date')
                ->take(4)
                ->get());

            $merchandise = $this->withBookmarks('merchandise', Merchandise::query()
                ->published()
                ->with('category')
                ->orderByDesc('popularity')
                ->take(8)
                ->get());

            $railProfile = $this->railProfile();
        } catch (Throwable $exception) {
            report($exception);
            $homeError = 'Some sections of the homepage could not be loaded.';
            $heroSlides = collect();
            $categories = collect();
            $trending = collect();
            $featuredCharacters = collect();
            $latestArticles = collect();
            $multimedia = collect();
            $upcomingEvents = collect();
            $upcomingReleases = collect();
            $communityPosts = collect();
            $merchandise = collect();
            $railProfile = null;
        }

        $fandoms = $categories->pluck('name')->all();

        return view('home', [
            'heroSlides' => $heroSlides,
            'categories' => $categories,
            'fandoms' => $fandoms,
            'trending' => $trending,
            'featuredCharacters' => $featuredCharacters,
            'latestArticles' => $latestArticles,
            'multimedia' => $multimedia,
            'upcomingEvents' => $upcomingEvents,
            'upcomingReleases' => $upcomingReleases,
            'communityPosts' => $communityPosts,
            'merchandise' => $merchandise,
            'railProfile' => $railProfile,
            'homeError' => $homeError,
        ]);
    }

    /**
     * GET /explore — server-side filtered content catalogue.
     */
    public function explore(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => trim((string) $request->query('category', '')),
            'type' => trim((string) $request->query('type', '')),
            'genre' => trim((string) $request->query('genre', '')),
            'year' => trim((string) $request->query('year', '')),
            'sort' => in_array((string) $request->query('sort'), self::SORTS, true)
                ? (string) $request->query('sort')
                : 'latest',
            'view' => $request->query('view') === 'list' ? 'list' : 'grid',
        ];

        $perPage = max(6, min(48, (int) $request->query('per_page', self::PER_PAGE)));

        $results = null;
        $error = null;
        $category = null;
        $facets = [
            'categories' => collect(),
            'genres' => collect(),
            'years' => collect(),
            'types' => collect(),
        ];
        $chips = [];

        try {
            $facets['categories'] = Category::query()->orderBy('name')->get(['id', 'name', 'slug']);
            $facets['genres'] = Content::query()
                ->published()
                ->whereNotNull('genre')
                ->where('genre', '!=', '')
                ->distinct()
                ->orderBy('genre')
                ->pluck('genre');
            $facets['years'] = Content::query()
                ->published()
                ->whereNotNull('release_date')
                ->pluck('release_date')
                ->map(fn ($date): int => $date instanceof CarbonInterface
                    ? $date->year
                    : (int) substr((string) $date, 0, 4))
                ->unique()
                ->sortDesc()
                ->values();
            $facets['types'] = Content::query()
                ->published()
                ->whereNotNull('content_type')
                ->distinct()
                ->orderBy('content_type')
                ->pluck('content_type');

            if ($filters['category'] !== '') {
                $category = $facets['categories']->firstWhere('slug', $filters['category']);
            }

            $query = Content::query()->published()->with('category');

            if ($filters['q'] !== '') {
                $query->search($filters['q']);
            }

            if ($filters['type'] !== '') {
                $query->where('content_type', $filters['type']);
            }

            if ($filters['genre'] !== '') {
                $query->where('genre', $filters['genre']);
            }

            if ($filters['year'] !== '' && ctype_digit($filters['year'])) {
                $query->whereYear('release_date', (int) $filters['year']);
            }

            if ($filters['category'] !== '') {
                $category
                    ? $query->where('category_id', $category->id)
                    : $query->whereRaw('1 = 0');
            }

            match ($filters['sort']) {
                'popular' => $query->orderByDesc('popularity')->orderByDesc('views_count'),
                'rating' => $query->orderByDesc('rating')->orderByDesc('popularity'),
                'alphabetical' => $query->orderBy('title'),
                default => $query->orderByDesc('release_date')->orderByDesc('id'),
            };

            $results = $query->paginate($perPage)->withQueryString();
            $this->attachBookmarks($results->getCollection());

            $chips = $this->activeChips($filters, $category);
        } catch (Throwable $exception) {
            report($exception);
            $error = 'We could not load the catalogue right now. Please try again.';
        }

        return view('explore', [
            'results' => $results,
            'filters' => $filters,
            'facets' => $facets,
            'chips' => $chips,
            'category' => $category,
            'error' => $error,
        ]);
    }

    /**
     * Hero slides: featured published titles first, topped up with the newest titles.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function heroSlides(): Collection
    {
        $slides = Content::query()
            ->published()
            ->with('category')
            ->featured()
            ->orderByDesc('popularity')
            ->orderByDesc('views_count')
            ->take(5)
            ->get();

        if ($slides->count() < 5) {
            $slides = $slides->concat(
                Content::query()
                    ->published()
                    ->with('category')
                    ->whereNotIn('id', $slides->pluck('id'))
                    ->orderByDesc('created_at')
                    ->take(5 - $slides->count())
                    ->get()
            );
        }

        return $slides->map(function (Content $content): array {
            $summary = trim((string) ($content->description ?: $content->synopsis));

            return [
                'id' => $content->id,
                'title' => $content->title,
                'subtitle' => collect([$content->genre, $content->content_type])->filter()->implode(' · '),
                'description' => Str::limit($summary, 150),
                'category' => $content->category?->name,
                'image' => img(
                    $content->banner_image ?: $content->cover_image,
                    $content->title,
                    1600,
                    640,
                    $content->category?->slug
                ),
                'cta' => 'Explore Now',
                'link' => route('content.show', $content->slug),
            ];
        })->values();
    }

    /**
     * Signed-in stats for the homepage rail. Guests get null.
     *
     * @return array{name: string, since: ?string, bookmarks: int, posts: int, ratings: int}|null
     */
    private function railProfile(): ?array
    {
        $user = auth()->user();

        if ($user === null) {
            return null;
        }

        return [
            'name' => $user->name,
            'since' => $user->created_at?->format('Y'),
            'bookmarks' => $user->bookmarks()->count(),
            'posts' => $user->submissions()->count(),
            'ratings' => Rating::query()->where('user_id', $user->id)->count(),
        ];
    }

    /**
     * Multimedia picks interleaved across video/trailer/audio/gallery types.
     *
     * @return Collection<int, Media>
     */
    private function mixedMedia(): Collection
    {
        $pool = Media::query()
            ->published()
            ->with('category')
            ->orderByDesc('views_count')
            ->take(24)
            ->get();

        $byType = $pool->groupBy('media_type');
        $mixed = collect();
        $round = 0;

        while ($mixed->count() < 8 && $byType->sum(fn ($items) => $items->count()) > 0) {
            foreach ($byType as $items) {
                $item = $items->values()->get($round);
                if ($item !== null && $mixed->count() < 8) {
                    $mixed->push($item);
                }
            }
            $round++;
            if ($round > 30) {
                break;
            }
        }

        return $this->withBookmarks('media', $mixed->take(8)->values());
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

    /**
     * Attach bookmark state to the current user's explore results.
     *
     * @param  Collection<int, Model>  $items
     */
    private function attachBookmarks(Collection $items): void
    {
        $this->withBookmarks('content', $items);
    }

    /**
     * Chips describing the filters currently applied on /explore.
     *
     * @param  array<string, string>  $filters
     * @return array<int, array{label: string, href: string}>
     */
    private function activeChips(array $filters, ?Category $category): array
    {
        $labels = [
            'q' => 'Search: "'.$filters['q'].'"',
            'category' => 'Category: '.($category?->name ?? $filters['category']),
            'type' => 'Type: '.$filters['type'],
            'genre' => 'Genre: '.$filters['genre'],
            'year' => 'Year: '.$filters['year'],
            'sort' => $filters['sort'] !== 'latest' ? 'Sorted by '.$filters['sort'] : null,
        ];

        $chips = [];

        foreach ($labels as $key => $label) {
            if ($label === null || $filters[$key] === '' || ($key === 'sort' && $filters['sort'] === 'latest')) {
                continue;
            }

            $query = array_filter([
                'q' => $filters['q'],
                'category' => $filters['category'],
                'type' => $filters['type'],
                'genre' => $filters['genre'],
                'year' => $filters['year'],
                'sort' => $filters['sort'] === 'latest' ? '' : $filters['sort'],
                'view' => $filters['view'] === 'list' ? 'list' : '',
            ]);

            unset($query[$key]);

            $chips[] = [
                'label' => $label,
                'href' => route('explore', $query),
            ];
        }

        return $chips;
    }
}
