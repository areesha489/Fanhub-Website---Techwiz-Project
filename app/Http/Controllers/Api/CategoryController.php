<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CategoryController extends ApiController
{
    /**
     * GET /api/categories — every category with its published content count.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()->orderBy('position')->get();

        $counts = $this->contentCounts();

        $mapped = $categories
            ->map(fn (Category $category): array => ApiSerializer::category($category, (int) ($counts[$category->id] ?? 0)))
            ->values()
            ->all();

        return response()->json([
            'data' => $mapped,
            'meta' => ApiSerializer::metaFor(count($mapped)),
        ]);
    }

    /**
     * GET /api/categories/{slug} — category detail plus its catalogue slice.
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        $counts = $this->contentCounts();
        $contents = Content::query()
            ->published()
            ->with(['category', 'tags'])
            ->where('category_id', $category->id)
            ->orderByDesc('popularity')
            ->take(12)
            ->get();

        $payload = ApiSerializer::category($category, (int) ($counts[$category->id] ?? 0));
        $payload['contents'] = $contents
            ->map(fn (Content $content): array => ApiSerializer::content($content, $this->bookmarkedMap('content', $contents)))
            ->values()
            ->all();
        $payload['counts'] = [
            'contents' => (int) ($counts[$category->id] ?? 0),
            'characters' => Character::query()->where('category_id', $category->id)->count(),
            'events' => $category->events()->count(),
            'articles' => $category->articles()->published()->count(),
            'merchandise' => $category->merchandise()->published()->count(),
            'releases' => $category->releases()->count(),
        ];

        return response()->json($this->data($payload));
    }

    /**
     * Published content counts keyed by category id (single grouped query).
     *
     * @return Collection<int, int>
     */
    private function contentCounts(): Collection
    {
        return Content::query()
            ->published()
            ->groupBy('category_id')
            ->selectRaw('category_id, COUNT(*) as total')
            ->pluck('total', 'category_id');
    }
}
