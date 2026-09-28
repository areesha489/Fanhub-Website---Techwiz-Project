<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Article;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArticleController extends AdminApiController
{
    /**
     * GET /api/admin/articles — editorial management list.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::query()->with(['category', 'author']);

        $this->applySearch($request, $query, ['title', 'excerpt']);
        $this->applyStatus($request, $query);
        $this->applyCategory($request, $query);

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        $query->orderByDesc('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();
        $map = $this->bookmarkedMap('article', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Article $article): array => ApiSerializer::article($article, $map))
        );
    }

    /**
     * POST /api/admin/articles — create an article.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'excerpt' => ['required', 'string', 'max:1000'],
            'body' => ['required', 'string', 'max:60000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['sometimes', 'string', Rule::in([Article::STATUS_DRAFT, Article::STATUS_PUBLISHED, Article::STATUS_ARCHIVED])],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
            'views_count' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $data['slug'] = $this->uniqueSlug('articles', $data['slug'] ?? $data['title']);
        $data['cover_image'] = $this->uploadImage($request, 'cover_image');
        $data['author_id'] = $data['author_id'] ?? $request->user()->id;
        $data['status'] = $data['status'] ?? Article::STATUS_PUBLISHED;
        $data['published_at'] = $data['published_at'] ?? now();
        $data['reading_time'] = $data['reading_time'] ?? max(1, (int) ceil(str_word_count(strip_tags($data['body'])) / 200));

        $article = Article::query()->create($data);

        return response()->json(['data' => ApiSerializer::article($article->load(['category', 'author']))], 201);
    }

    /**
     * PUT/PATCH /api/admin/articles/{article} — update (bound by slug).
     */
    public function update(Request $request, Article $article): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'excerpt' => ['sometimes', 'string', 'max:1000'],
            'body' => ['sometimes', 'string', 'max:60000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['sometimes', 'string', Rule::in([Article::STATUS_DRAFT, Article::STATUS_PUBLISHED, Article::STATUS_ARCHIVED])],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
            'views_count' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        if (array_key_exists('slug', $data) || array_key_exists('title', $data)) {
            $data['slug'] = $this->uniqueSlug('articles', $data['slug'] ?? $data['title'], $article->id);
        }

        $data['cover_image'] = $this->uploadImage($request, 'cover_image', $article->cover_image);

        if (array_key_exists('status', $data)
            && $data['status'] === Article::STATUS_PUBLISHED
            && $article->published_at === null) {
            $data['published_at'] = now();
        }

        $article->update($data);

        return response()->json(['data' => ApiSerializer::article($article->fresh(['category', 'author']))]);
    }

    /**
     * DELETE /api/admin/articles/{article} — soft delete.
     */
    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json(['message' => 'Article deleted.']);
    }
}
