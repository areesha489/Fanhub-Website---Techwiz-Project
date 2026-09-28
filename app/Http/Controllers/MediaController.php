<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Media;
use App\Support\BookmarkHelper;
use App\Support\Engagement;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Cards rendered per catalogue page.
     */
    private const PER_PAGE = 12;

    /**
     * Tab keys accepted by the library.
     *
     * @var list<string>
     */
    private const TYPES = ['video', 'trailer', 'audio', 'gallery'];

    /**
     * Sort keys accepted by the library.
     *
     * @var list<string>
     */
    private const SORTS = ['latest', 'popular', 'rating'];

    /**
     * Multimedia centre: type tabs, free-text search and sorting.
     */
    public function index(Request $request): View
    {
        $requestedType = (string) $request->query('type', 'video');
        $type = in_array($requestedType, self::TYPES, true) ? $requestedType : 'video';
        $search = trim((string) $request->query('q', ''));
        $requestedSort = (string) $request->query('sort', 'latest');
        $sort = in_array($requestedSort, self::SORTS, true) ? $requestedSort : 'latest';

        $categories = collect();
        $media = null;
        $counts = collect();
        $activeCategory = null;
        $error = null;

        try {
            $categories = Category::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'accent']);

            $activeCategory = $categories->firstWhere('slug', (string) $request->query('category', ''));

            $counts = Media::query()
                ->published()
                ->toBase()
                ->selectRaw('media_type, COUNT(*) as aggregate')
                ->groupBy('media_type')
                ->pluck('aggregate', 'media_type');

            $query = Media::query()->published()->with('category')->where('media_type', $type);

            if ($search !== '') {
                $query->search($search);
            }

            if ($request->filled('category')) {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', (string) $request->query('category')));
            }

            match ($sort) {
                'popular' => $query->orderByDesc('views_count')->orderByDesc('id'),
                'rating' => $query->orderByDesc('rating')->orderByDesc('views_count'),
                default => $query->orderByDesc('created_at')->orderByDesc('id'),
            };

            $media = $query->paginate(self::PER_PAGE)->withQueryString();
            $this->applyBookmarks($media, 'media');
        } catch (\Throwable $exception) {
            Log::warning('media.index failed: '.$exception->getMessage());
            $error = 'We could not load the multimedia library right now. Please refresh the page or try again in a moment.';
        }

        return view('multimedia.index', [
            'media' => $media,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'counts' => $counts,
            'type' => $type,
            'search' => $search,
            'sort' => $sort,
            'total' => $media?->total() ?? 0,
            'error' => $error,
        ]);
    }

    /**
     * Media detail: player, audio card or gallery grid depending on the type.
     */
    public function show(string $slug): View
    {
        try {
            $item = Media::query()
                ->with(['category'])
                ->where('slug', $slug)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            abort(404);
        } catch (\Throwable $exception) {
            Log::warning('media.show failed: '.$exception->getMessage());

            return view('multimedia.show', [
                'media' => null,
                'loadError' => 'We could not load this media entry right now. Please try again in a moment.',
            ]);
        }

        Engagement::recordView($item, 'media');

        $related = collect();
        $galleryImages = collect();
        $isBookmarked = false;
        $bookmarkId = null;
        $embedUrl = null;
        $fileUrl = null;

        try {
            $galleryImages = collect($item->gallery ?? [])->filter()->values();
            $embedUrl = $this->embedUrlFor($item);
            $fileUrl = $this->fileUrlFor($item);
            $bookmarkId = BookmarkHelper::bookmarkIdFor('media', (int) $item->id);
            $isBookmarked = $bookmarkId !== null;

            $related = Media::query()
                ->published()
                ->with(['category'])
                ->where('id', '!=', $item->id)
                ->where(function ($relatedQuery) use ($item): void {
                    $relatedQuery->where('media_type', $item->media_type)
                        ->orWhere('category_id', $item->category_id);
                })
                ->orderByDesc('views_count')
                ->limit(6)
                ->get();

            if ($related->isEmpty()) {
                $related = Media::query()
                    ->published()
                    ->with(['category'])
                    ->where('id', '!=', $item->id)
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get();
            }
        } catch (\Throwable $exception) {
            Log::warning('media.show extras failed: '.$exception->getMessage());
        }

        return view('multimedia.show', [
            'media' => $item,
            'related' => $related,
            'galleryImages' => $galleryImages,
            'embedUrl' => $embedUrl,
            'fileUrl' => $fileUrl,
            'isBookmarked' => $isBookmarked,
            'bookmarkId' => $bookmarkId,
            'loadError' => null,
        ]);
    }

    /**
     * YouTube/Vimeo embed URL when the stored source is embeddable, otherwise null.
     */
    private function embedUrlFor(Media $media): ?string
    {
        $source = $media->embed_url ?: $media->url;

        if (! $source) {
            return null;
        }

        $lower = strtolower($source);
        $isProvider = Str::contains($lower, ['youtube.', 'youtu.be', 'vimeo.com']);

        if (preg_match('#^https?://#i', $source) === 1) {
            return $isProvider ? Engagement::embedUrl($source) : null;
        }

        // Bare provider id (e.g. "dQw4w9WgXcQ") stored in embed_url.
        return preg_match('/^[A-Za-z0-9_-]{6,}$/', $source) === 1 ? Engagement::embedUrl($source) : null;
    }

    /**
     * Public URL for an uploaded or direct video file, otherwise null.
     */
    private function fileUrlFor(Media $media): ?string
    {
        $source = $media->url;

        if (! is_string($source) || $source === '') {
            return null;
        }

        if (str_starts_with($source, 'media/videos/')) {
            return asset('storage/'.$source);
        }

        $lower = strtolower($source);

        if (Str::contains($lower, ['youtube.', 'youtu.be', 'vimeo.com'])) {
            return null;
        }

        $path = parse_url($source, PHP_URL_PATH) ?? $source;

        return preg_match('/\.(mp4|webm|ogg|mov|m4v)$/i', $path) === 1 ? $source : null;
    }

    /**
     * Preload bookmark state for a page of cards in a single query.
     *
     * @param  LengthAwarePaginator<int, Media>  $paginator
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
