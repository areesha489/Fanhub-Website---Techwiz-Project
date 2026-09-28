<?php

namespace App\Http\Controllers\Api;

use App\Models\Content;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends ApiController
{
    /**
     * GET /api/content — filterable, paginated catalogue.
     *
     * Query params: q, category (slug|id), type, genre, featured, sort, per_page, page.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Content::query()->published()->with(['category', 'tags']);

        $q = trim((string) $request->input('q'));
        if ($q !== '') {
            $query->search($q);
        }

        if ($categoryId = $this->categoryId($request->input('category'))) {
            $query->where('category_id', $categoryId);
        }

        if ($type = $request->input('type')) {
            $query->where('content_type', $type);
        }

        if ($genre = $request->input('genre')) {
            $query->where('genre', $genre);
        }

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        match ($request->input('sort')) {
            'newest' => $query->latest('release_date'),
            'rating' => $query->orderByDesc('rating'),
            'views' => $query->orderByDesc('views_count'),
            'title' => $query->orderBy('title'),
            default => $query->orderByDesc('popularity'),
        };

        $paginator = $query->paginate($this->perPage($request))->withQueryString();
        $map = $this->bookmarkedMap('content', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Content $content): array => ApiSerializer::content($content, $map))
        );
    }

    /**
     * GET /api/content/{slug} — published title detail.
     */
    public function show(string $slug): JsonResponse
    {
        $content = Content::query()
            ->published()
            ->with(['category', 'tags', 'characters.category'])
            ->where('slug', $slug)
            ->firstOrFail();

        $map = $this->bookmarkedMap('content', [$content]);

        return response()->json($this->data(ApiSerializer::content($content, $map, detail: true)));
    }
}
