<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Release;
use App\Support\BookmarkHelper;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Global search across every public content type.
 */
class SearchController extends Controller
{
    /**
     * Result buckets shown as tabs.
     *
     * @var list<string>
     */
    private const TYPES = ['content', 'article', 'character', 'media', 'event', 'merchandise', 'release'];

    /**
     * Sort orders accepted by /search.
     *
     * @var list<string>
     */
    private const SORTS = ['relevance', 'latest', 'popular', 'date'];

    /**
     * Results per page.
     */
    private const PER_PAGE = 12;

    /**
     * GET /search - global search over contents, articles, characters, media,
     * events, merchandise and releases.
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $requestedType = (string) $request->query('type', 'all');
        $type = in_array($requestedType, array_merge(['all'], self::TYPES), true) ? $requestedType : 'all';
        $categorySlug = trim((string) $request->query('category', ''));
        $genre = trim((string) $request->query('genre', ''));
        $yearInput = trim((string) $request->query('year', ''));
        $year = ctype_digit($yearInput) ? (int) $yearInput : null;
        $requestedSort = (string) $request->query('sort', '');
        $sort = in_array($requestedSort, self::SORTS, true)
            ? $requestedSort
            : ($q !== '' ? 'relevance' : 'latest');

        $hasQuery = $q !== '' || $type !== 'all' || $categorySlug !== '' || $genre !== '' || $year !== null;

        $category = $categorySlug !== '' && $hasQuery
            ? Category::query()->where('slug', $categorySlug)->first()
            : null;

        $filters = [
            'q' => $q,
            'categoryId' => $category?->id,
            'genre' => $genre,
            'year' => $year,
        ];

        $counts = array_fill_keys(self::TYPES, 0);
        $total = 0;
        $results = null;
        $rows = collect();
        $error = null;
        $facets = ['categories' => collect(), 'genres' => collect(), 'years' => collect()];

        try {
            $facets['categories'] = Category::query()->orderBy('name')->get(['id', 'name', 'slug', 'icon']);
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

            if ($hasQuery) {
                $counts = $this->typeCounts($filters);
                $total = $type === 'all' ? array_sum($counts) : (int) ($counts[$type] ?? 0);

                if ($type === 'all') {
                    $union = $this->unionQuery($filters);

                    if ($union !== null) {
                        $this->applyUnionSort($union, $sort);
                        $paginator = $union->paginate(self::PER_PAGE, ['*'], 'page')->withQueryString();
                        $rows = $this->hydrateRows($paginator->items());
                        $results = $paginator;
                    }
                } else {
                    $query = $this->typeQuery($type, $filters, $sort);
                    $results = $query?->paginate(self::PER_PAGE)->withQueryString();
                }
            }
        } catch (Throwable $exception) {
            report($exception);
            $error = 'Search is unavailable right now. Please try again in a moment.';
            $results = null;
            $rows = collect();
        }

        return view('search', [
            'q' => $q,
            'type' => $type,
            'sort' => $sort,
            'category' => $category,
            'categorySlug' => $categorySlug,
            'genre' => $genre,
            'year' => $year,
            'hasQuery' => $hasQuery,
            'counts' => $counts,
            'total' => $total,
            'results' => $results,
            'rows' => $rows,
            'facets' => $facets,
            'error' => $error,
        ]);
    }

    /**
     * Column map for every searchable type (shared by counts, union and typed queries).
     *
     * @return array<string, array{
     *     model: class-string<Model>,
     *     title: string, slug: string, image: string, excerpt: string,
     *     views: string, popularity: string, date: string|null, latest: string,
     *     status: array{0: string, 1: string}|null, genre: bool,
     *     like: list<string>, route: string, label: string
     * }>
     */
    private function sourceMap(): array
    {
        return [
            'content' => [
                'model' => Content::class,
                'title' => 'title', 'slug' => 'slug', 'image' => 'cover_image', 'excerpt' => 'description',
                'views' => 'views_count', 'popularity' => 'popularity', 'date' => 'release_date', 'latest' => 'created_at',
                'status' => ['status', Content::STATUS_PUBLISHED], 'genre' => true,
                'like' => ['title', 'genre', 'description'], 'route' => 'content.show', 'label' => 'Content',
            ],
            'article' => [
                'model' => Article::class,
                'title' => 'title', 'slug' => 'slug', 'image' => 'cover_image', 'excerpt' => 'excerpt',
                'views' => 'views_count', 'popularity' => 'views_count', 'date' => 'published_at', 'latest' => 'published_at',
                'status' => ['status', 'published'], 'genre' => false,
                'like' => ['title', 'excerpt', 'body'], 'route' => 'articles.show', 'label' => 'Article',
            ],
            'character' => [
                'model' => Character::class,
                'title' => 'name', 'slug' => 'slug', 'image' => 'image', 'excerpt' => 'bio',
                'views' => 'views_count', 'popularity' => 'popularity', 'date' => null, 'latest' => 'created_at',
                'status' => null, 'genre' => false,
                'like' => ['name', 'fandom', 'bio'], 'route' => 'characters.show', 'label' => 'Character',
            ],
            'media' => [
                'model' => Media::class,
                'title' => 'title', 'slug' => 'slug', 'image' => 'thumbnail', 'excerpt' => 'description',
                'views' => 'views_count', 'popularity' => 'views_count', 'date' => null, 'latest' => 'created_at',
                'status' => ['status', 'published'], 'genre' => false,
                'like' => ['title', 'description'], 'route' => 'media.show', 'label' => 'Media',
            ],
            'event' => [
                'model' => Event::class,
                'title' => 'name', 'slug' => 'slug', 'image' => 'cover_image', 'excerpt' => 'description',
                'views' => 'views_count', 'popularity' => 'views_count', 'date' => 'start_date', 'latest' => 'created_at',
                'status' => null, 'genre' => false,
                'like' => ['name', 'description', 'city', 'venue'], 'route' => 'events.show', 'label' => 'Event',
            ],
            'merchandise' => [
                'model' => Merchandise::class,
                'title' => 'title', 'slug' => 'slug', 'image' => 'image', 'excerpt' => 'description',
                'views' => 'popularity', 'popularity' => 'popularity', 'date' => 'release_date', 'latest' => 'created_at',
                'status' => ['status', 'published'], 'genre' => false,
                'like' => ['title', 'fandom', 'description'], 'route' => 'merchandise.show', 'label' => 'Merchandise',
            ],
            'release' => [
                'model' => Release::class,
                'title' => 'title', 'slug' => 'slug', 'image' => 'poster', 'excerpt' => 'description',
                'views' => 'popularity', 'popularity' => 'popularity', 'date' => 'release_date', 'latest' => 'created_at',
                'status' => null, 'genre' => false,
                'like' => ['title', 'description'], 'route' => 'releases.index', 'label' => 'Release',
            ],
        ];
    }

    /**
     * Per-type result counts for the tab bar (a single union query).
     *
     * @param  array{q: string, categoryId: int|null, genre: string, year: int|null}  $filters
     * @return array<string, int>
     */
    private function typeCounts(array $filters): array
    {
        $counts = array_fill_keys(self::TYPES, 0);
        $parts = [];

        foreach ($this->sourceMap() as $type => $config) {
            $query = $this->filteredTableQuery($config, $filters);
            if ($query === null) {
                continue;
            }

            $parts[$type] = $query->selectRaw("'{$type}' as type, COUNT(*) as total_count");
        }

        if ($parts === []) {
            return $counts;
        }

        $first = array_shift($parts);
        foreach ($parts as $part) {
            $first->unionAll($part);
        }

        foreach ($first->get() as $row) {
            $counts[$row->type] = (int) $row->total_count;
        }

        return $counts;
    }

    /**
     * All matching rows across types, unified into one paginated query.
     *
     * @param  array{q: string, categoryId: int|null, genre: string, year: int|null}  $filters
     * @return \Illuminate\Database\Query\Builder|null
     */
    private function unionQuery(array $filters)
    {
        $parts = [];

        foreach ($this->sourceMap() as $type => $config) {
            $query = $this->filteredTableQuery($config, $filters);
            if ($query === null) {
                continue;
            }

            $releaseOn = $config['date'] !== null ? "{$config['date']} as release_on" : 'null as release_on';
            $contentId = $type === 'release' ? 'content_id' : 'null as content_id';

            $parts[$type] = $query->selectRaw(
                "'{$type}' as type, id, {$config['title']} as title, {$config['slug']} as slug, "
                ."category_id, {$config['image']} as image, {$config['excerpt']} as excerpt, "
                ."{$config['views']} as views, {$releaseOn}, {$config['latest']} as sort_on, {$contentId}"
            );
        }

        if ($parts === []) {
            return null;
        }

        $first = array_shift($parts);
        foreach ($parts as $part) {
            $first->unionAll($part);
        }

        return $first;
    }

    /**
     * Base WHERE clause for one type, or null when the type cannot match the filters.
     *
     * @param  array<string, mixed>  $config
     * @param  array{q: string, categoryId: int|null, genre: string, year: int|null}  $filters
     * @return \Illuminate\Database\Query\Builder|null
     */
    private function filteredTableQuery(array $config, array $filters)
    {
        $modelClass = $config['model'];
        $query = DB::table((new $modelClass)->getTable());

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->whereNull('deleted_at');
        }

        if ($config['status'] !== null) {
            $query->where($config['status'][0], $config['status'][1]);
        }

        if ($filters['categoryId'] !== null) {
            $query->where('category_id', $filters['categoryId']);
        }

        if ($filters['genre'] !== '') {
            if (! $config['genre']) {
                return null;
            }

            $query->where('genre', $filters['genre']);
        }

        if ($filters['year'] !== null) {
            if ($config['date'] === null) {
                return null;
            }

            $query->whereYear($config['date'], $filters['year']);
        }

        if ($filters['q'] !== '') {
            $this->applyTextFilter($query, $filters['q'], $config['like']);
        }

        return $query;
    }

    /**
     * Typed (single model) result query, or null when the filters cannot match.
     *
     * @param  array{q: string, categoryId: int|null, genre: string, year: int|null}  $filters
     * @return Builder<Model>|null
     */
    private function typeQuery(string $type, array $filters, string $sort)
    {
        $config = $this->sourceMap()[$type];
        $query = $config['model']::query();

        if ($config['status'] !== null) {
            $query->where($config['status'][0], $config['status'][1]);
        }

        if ($filters['categoryId'] !== null) {
            $query->where('category_id', $filters['categoryId']);
        }

        if ($filters['genre'] !== '') {
            if (! $config['genre']) {
                return null;
            }

            $query->where('genre', $filters['genre']);
        }

        if ($filters['year'] !== null) {
            if ($config['date'] === null) {
                return null;
            }

            $query->whereYear($config['date'], $filters['year']);
        }

        if ($filters['q'] !== '') {
            $this->applyTextFilter($query, $filters['q'], $config['like']);
        }

        $query->with('category');

        match ($sort) {
            'popular' => $query->orderByDesc($config['popularity'])->orderByDesc($config['views']),
            'latest' => $query->orderByDesc($config['latest']),
            'date' => $query->orderByDesc($config['date'] ?? $config['latest']),
            default => $query->orderByDesc($config['views']),
        };

        return $query;
    }

    /**
     * Ordering for the unified (union) result set.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    private function applyUnionSort($query, string $sort): void
    {
        match ($sort) {
            'date' => $query->orderByDesc('release_on'),
            'latest' => $query->orderByDesc('sort_on'),
            default => $query->orderByDesc('views'),
        };
    }

    /**
     * Case-insensitive free-text match with LIKE wildcards escaped.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<*>  $query
     * @param  list<string>  $columns
     */
    private function applyTextFilter($query, string $term, array $columns): void
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
        $like = '%'.$escaped.'%';

        $query->where(function ($builder) use ($columns, $like): void {
            foreach ($columns as $index => $column) {
                $index === 0
                    ? $builder->where($column, 'like', $like)
                    : $builder->orWhere($column, 'like', $like);
            }
        });
    }

    /**
     * Turn raw union rows into display rows (links, thumbnails, category names).
     *
     * @param  array<int, object>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private function hydrateRows(array $items): Collection
    {
        $categoryIds = array_values(array_unique(array_filter(array_column($items, 'category_id'))));
        $categories = $categoryIds === []
            ? collect()
            : Category::query()->whereIn('id', $categoryIds)->get(['id', 'name', 'slug', 'icon'])->keyBy('id');

        $releaseContentIds = array_values(array_unique(array_filter(
            array_map(
                fn (object $row) => $row->type === 'release' ? $row->content_id : null,
                $items
            )
        )));
        $contentSlugs = $releaseContentIds === []
            ? collect()
            : Content::query()->whereIn('id', $releaseContentIds)->pluck('slug', 'id');

        return collect($items)->map(function (object $row) use ($categories, $contentSlugs): array {
            $category = $categories[$row->category_id] ?? null;

            return [
                'type' => $row->type,
                'type_label' => BookmarkHelper::typeLabel($row->type),
                'title' => (string) $row->title,
                'url' => $this->urlForRow($row, $contentSlugs),
                'image' => img($row->image, (string) $row->title, 480, 320, $category?->slug),
                'excerpt' => Str::limit(trim(strip_tags((string) $row->excerpt)), 150),
                'category' => $category?->name,
                'category_slug' => $category?->slug,
                'views' => (int) $row->views,
                'date' => $row->release_on,
            ];
        })->values();
    }

    /**
     * Public URL for a unified result row.
     *
     * @param  Collection<int, string>  $contentSlugs  id => slug map for releases tied to a title
     */
    private function urlForRow(object $row, Collection $contentSlugs): string
    {
        if ($row->type === 'release') {
            $slug = $row->content_id ? ($contentSlugs[$row->content_id] ?? null) : null;

            return $slug
                ? route('content.show', $slug)
                : route('releases.index');
        }

        $config = $this->sourceMap()[$row->type];

        return route($config['route'], $row->slug);
    }
}
