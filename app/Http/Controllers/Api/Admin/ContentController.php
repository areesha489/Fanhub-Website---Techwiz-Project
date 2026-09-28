<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Content;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContentController extends AdminApiController
{
    /**
     * GET /api/admin/content — catalogue management list.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Content::query()->with(['category', 'tags']);

        $this->applySearch($request, $query, ['title', 'genre', 'description']);
        $this->applyStatus($request, $query);
        $this->applyCategory($request, $query);

        if ($type = $request->input('type')) {
            $query->where('content_type', $type);
        }

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        $query->orderByDesc('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();
        $map = $this->bookmarkedMap('content', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Content $content): array => ApiSerializer::content($content, $map))
        );
    }

    /**
     * POST /api/admin/content — create a title.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug('contents', $data['slug'] ?? $data['title']);
        $data['cover_image'] = $this->uploadImage($request, 'cover_image');
        $data['banner_image'] = $this->uploadImage($request, 'banner_image');
        $data['created_by'] = $request->user()->id;
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $content = Content::query()->create($data);

        if ($tagIds !== []) {
            $content->tags()->sync($tagIds);
        }

        return response()->json(['data' => ApiSerializer::content($content->load(['category', 'tags']))], 201);
    }

    /**
     * PUT/PATCH /api/admin/content/{content} — update a title (bound by slug).
     */
    public function update(Request $request, Content $content): JsonResponse
    {
        $data = $this->validated($request);

        if (array_key_exists('slug', $data) || array_key_exists('title', $data)) {
            $data['slug'] = $this->uniqueSlug('contents', $data['slug'] ?? $data['title'], $content->id);
        }

        $data['cover_image'] = $this->uploadImage($request, 'cover_image', $content->cover_image);
        $data['banner_image'] = $this->uploadImage($request, 'banner_image', $content->banner_image);
        $tagIds = $data['tag_ids'] ?? null;
        unset($data['tag_ids']);

        $content->update($data);

        if ($tagIds !== null) {
            $content->tags()->sync($tagIds);
        }

        return response()->json(['data' => ApiSerializer::content($content->fresh(['category', 'tags']))]);
    }

    /**
     * DELETE /api/admin/content/{content} — soft delete.
     */
    public function destroy(Content $content): JsonResponse
    {
        $content->delete();

        return response()->json(['message' => 'Content deleted.']);
    }

    /**
     * Shared validation for store/update.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'content_type' => ['required', 'string', Rule::in(Content::TYPES)],
            'genre' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'synopsis' => ['nullable', 'string', 'max:5000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'release_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in([Content::STATUS_DRAFT, Content::STATUS_PUBLISHED, Content::STATUS_ARCHIVED])],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'views_count' => ['nullable', 'integer', 'min:0'],
            'popularity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'featured' => ['nullable', 'boolean'],
            'trailer_url' => ['nullable', 'string', 'max:500'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ]);

        return $data;
    }
}
