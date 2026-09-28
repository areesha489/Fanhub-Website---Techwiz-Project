<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends ApiController
{
    /**
     * GET /api/articles — published articles, newest first.
     *
     * Query params: q, category (slug|id), featured, sort, per_page, page.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::query()->published()->with(['category', 'author']);

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
            'oldest' => $query->oldest('published_at'),
            default => $query->latest('published_at'),
        };

        $paginator = $query->paginate($this->perPage($request))->withQueryString();
        $map = $this->bookmarkedMap('article', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Article $article): array => ApiSerializer::article($article, $map))
        );
    }

    /**
     * GET /api/articles/{slug} — article detail with body, tags and related.
     */
    public function show(string $slug): JsonResponse
    {
        $article = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        $map = $this->bookmarkedMap('article', [$article]);

        return response()->json($this->data(ApiSerializer::article($article, $map, detail: true)));
    }
}
