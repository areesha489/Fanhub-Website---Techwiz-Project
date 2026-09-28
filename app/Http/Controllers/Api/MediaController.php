<?php

namespace App\Http\Controllers\Api;

use App\Models\Media;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends ApiController
{
    /**
     * GET /api/media — filterable multimedia library.
     *
     * Query params: type (video|trailer|audio|gallery), q, category, featured, sort, per_page, page.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Media::query()->published()->with('category');

        if ($type = $request->input('type')) {
            $query->where('media_type', $type);
        }

        $q = trim((string) $request->input('q'));
        if ($q !== '') {
            $query->search($q);
        }

        if ($categoryId = $this->categoryId($request->input('category'))) {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        match ($request->input('sort')) {
            'views' => $query->orderByDesc('views_count'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->latest('id'),
        };

        $paginator = $query->paginate($this->perPage($request))->withQueryString();
        $map = $this->bookmarkedMap('media', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Media $media): array => ApiSerializer::media($media, $map))
        );
    }

    /**
     * GET /api/media/{slug} — media detail with gallery images.
     */
    public function show(string $slug): JsonResponse
    {
        $media = Media::query()
            ->published()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        $map = $this->bookmarkedMap('media', [$media]);

        return response()->json($this->data(ApiSerializer::media($media, $map, detail: true)));
    }
}
