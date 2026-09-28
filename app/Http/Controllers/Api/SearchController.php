<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Release;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends ApiController
{
    /**
     * GET /api/search?q=... — grouped results across every public entity type.
     */
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        $limit = min(24, max(1, (int) $request->input('limit', 8)));

        if ($q === '') {
            return response()->json([
                'data' => [
                    'query' => '',
                    'contents' => [],
                    'characters' => [],
                    'articles' => [],
                    'media' => [],
                    'events' => [],
                    'merchandise' => [],
                    'releases' => [],
                ],
                'meta' => ApiSerializer::metaFor(0),
            ]);
        }

        $contents = Content::query()->published()->with(['category', 'tags'])->search($q)->orderByDesc('views_count')->take($limit)->get();
        $characters = Character::query()->with('category')->search($q)->orderByDesc('popularity')->take($limit)->get();
        $articles = Article::query()->published()->with(['category', 'author'])->search($q)->latest('published_at')->take($limit)->get();
        $media = Media::query()->published()->with('category')->search($q)->latest('id')->take($limit)->get();
        $events = Event::query()->with('category')->search($q)->orderBy('start_date')->take($limit)->get();
        $merchandise = Merchandise::query()->published()->with('category')->search($q)->orderByDesc('popularity')->take($limit)->get();
        $releases = Release::query()->with('category')->search($q)->orderBy('release_date')->take($limit)->get();

        $contentMap = $this->bookmarkedMap('content', $contents);
        $characterMap = $this->bookmarkedMap('character', $characters);
        $articleMap = $this->bookmarkedMap('article', $articles);
        $mediaMap = $this->bookmarkedMap('media', $media);
        $eventMap = $this->bookmarkedMap('event', $events);
        $merchandiseMap = $this->bookmarkedMap('merchandise', $merchandise);
        $releaseMap = $this->bookmarkedMap('release', $releases);

        $payload = [
            'query' => $q,
            'contents' => $contents->map(fn (Content $item): array => ApiSerializer::content($item, $contentMap))->values()->all(),
            'characters' => $characters->map(fn (Character $item): array => ApiSerializer::character($item, $characterMap))->values()->all(),
            'articles' => $articles->map(fn (Article $item): array => ApiSerializer::article($item, $articleMap))->values()->all(),
            'media' => $media->map(fn (Media $item): array => ApiSerializer::media($item, $mediaMap))->values()->all(),
            'events' => $events->map(fn (Event $item): array => ApiSerializer::event($item, $eventMap))->values()->all(),
            'merchandise' => $merchandise->map(fn (Merchandise $item): array => ApiSerializer::merchandise($item, $merchandiseMap))->values()->all(),
            'releases' => $releases->map(fn (Release $item): array => ApiSerializer::release($item, $releaseMap))->values()->all(),
        ];

        $total = count($payload['contents']) + count($payload['characters']) + count($payload['articles'])
            + count($payload['media']) + count($payload['events']) + count($payload['merchandise'])
            + count($payload['releases']);

        return response()->json([
            'data' => $payload,
            'meta' => ApiSerializer::metaFor($total, $total),
        ]);
    }
}
