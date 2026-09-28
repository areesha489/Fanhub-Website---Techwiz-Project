<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Media;
use App\Support\BookmarkHelper;
use App\Support\Engagement;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Public content detail page (trailer, tabs, related titles).
 */
class ContentController extends Controller
{
    /**
     * GET /content/{slug}
     */
    public function show(string $slug): View
    {
        $content = Content::query()
            ->published()
            ->with(['category', 'tags', 'characters.category'])
            ->where('slug', $slug)
            ->firstOrFail();

        Engagement::recordView($content, 'content');
        $content->views_count = (int) $content->views_count + 1;

        $bookmarkId = BookmarkHelper::bookmarkIdFor('content', (int) $content->id);
        $trailerEmbed = $content->trailer_url ? Engagement::embedUrl($content->trailer_url) : null;

        $error = null;
        $related = collect();
        $relatedMedia = collect();

        try {
            $related = $this->withBookmarks('content', $this->relatedContents($content));

            // The Content model has no media() relation — show the category's media.
            $relatedMedia = $this->withBookmarks('media', $content->category_id
                ? Media::query()
                    ->published()
                    ->with('category')
                    ->where('category_id', $content->category_id)
                    ->orderByDesc('views_count')
                    ->take(8)
                    ->get()
                : collect());
        } catch (Throwable $exception) {
            report($exception);
            $error = 'Extra sections of this page could not be loaded.';
        }

        $characters = $this->withBookmarks('character', $content->characters);
        $miniRelated = $related->take(5)->values();

        return view('content.show', [
            'content' => $content,
            'bookmarkId' => $bookmarkId,
            'trailerEmbed' => $trailerEmbed,
            'related' => $related,
            'relatedMedia' => $relatedMedia,
            'characters' => $characters,
            'miniRelated' => $miniRelated,
            'error' => $error,
        ]);
    }

    /**
     * Titles from the same category (newest, most popular first) with a newest-titles fallback.
     *
     * @return Collection<int, Content>
     */
    private function relatedContents(Content $content): Collection
    {
        $related = Content::query()
            ->published()
            ->with('category')
            ->where('category_id', $content->category_id)
            ->where('id', '!=', $content->id)
            ->orderByDesc('popularity')
            ->orderByDesc('views_count')
            ->take(6)
            ->get();

        if ($related->count() < 6) {
            $related = $related->concat(
                Content::query()
                    ->published()
                    ->with('category')
                    ->where('id', '!=', $content->id)
                    ->whereNotIn('id', $related->pluck('id'))
                    ->orderByDesc('created_at')
                    ->take(6 - $related->count())
                    ->get()
            );
        }

        return $related->take(6)->values();
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
