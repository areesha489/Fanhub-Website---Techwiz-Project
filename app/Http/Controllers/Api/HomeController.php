<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Release;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;

class HomeController extends ApiController
{
    /**
     * GET /api/home — hero slides plus every home page section.
     */
    public function index(): JsonResponse
    {
        $featured = Content::query()
            ->published()
            ->with('category')
            ->orderByDesc('popularity')
            ->take(5)
            ->get();

        if ($featured->isEmpty()) {
            $featured = Content::query()
                ->published()
                ->with('category')
                ->orderByDesc('views_count')
                ->take(5)
                ->get();
        }

        $trending = Content::query()
            ->published()
            ->with('category', 'tags')
            ->orderByDesc('views_count')
            ->take(10)
            ->get();

        $characters = Character::query()
            ->featured()
            ->with('category')
            ->orderByDesc('popularity')
            ->take(6)
            ->get();

        if ($characters->isEmpty()) {
            $characters = Character::query()
                ->with('category')
                ->latest('id')
                ->take(6)
                ->get();
        }

        $articles = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->take(6)
            ->get();

        $multimedia = Media::query()
            ->published()
            ->with('category')
            ->latest('id')
            ->take(6)
            ->get();

        $events = Event::query()
            ->upcoming()
            ->with('category')
            ->orderBy('start_date')
            ->take(6)
            ->get();

        $releases = Release::query()
            ->upcoming()
            ->with('category')
            ->orderBy('release_date')
            ->take(6)
            ->get();

        $merchandise = Merchandise::query()
            ->published()
            ->with('category')
            ->orderByDesc('popularity')
            ->take(6)
            ->get();

        $categories = Category::query()->orderBy('position')->get();

        $contentCounts = Content::query()
            ->published()
            ->groupBy('category_id')
            ->selectRaw('category_id, COUNT(*) as total')
            ->pluck('total', 'category_id');

        $trendingMap = $this->bookmarkedMap('content', $trending->concat($featured)->unique('id'));
        $characterMap = $this->bookmarkedMap('character', $characters);
        $articleMap = $this->bookmarkedMap('article', $articles);
        $mediaMap = $this->bookmarkedMap('media', $multimedia);
        $eventMap = $this->bookmarkedMap('event', $events);
        $releaseMap = $this->bookmarkedMap('release', $releases);
        $merchandiseMap = $this->bookmarkedMap('merchandise', $merchandise);

        $payload = [
            'hero' => $featured->map(fn (Content $content): array => ApiSerializer::heroSlide($content))->values()->all(),
            'categories' => $categories
                ->map(fn (Category $category): array => ApiSerializer::category(
                    $category,
                    (int) ($contentCounts[$category->id] ?? 0)
                ))
                ->values()
                ->all(),
            'trending' => $trending->map(fn (Content $content): array => ApiSerializer::content($content, $trendingMap))->values()->all(),
            'featured_characters' => $characters->map(fn (Character $character): array => ApiSerializer::character($character, $characterMap))->values()->all(),
            'latest_articles' => $articles->map(fn (Article $article): array => ApiSerializer::article($article, $articleMap))->values()->all(),
            'multimedia' => $multimedia->map(fn (Media $media): array => ApiSerializer::media($media, $mediaMap))->values()->all(),
            'events' => $events->map(fn (Event $event): array => ApiSerializer::event($event, $eventMap))->values()->all(),
            'releases' => $releases->map(fn (Release $release): array => ApiSerializer::release($release, $releaseMap))->values()->all(),
            'merchandise' => $merchandise->map(fn (Merchandise $item): array => ApiSerializer::merchandise($item, $merchandiseMap))->values()->all(),
        ];

        return response()->json($this->data($payload));
    }
}
