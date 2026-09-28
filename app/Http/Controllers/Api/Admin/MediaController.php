<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Media;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MediaController extends AdminApiController
{
    /**
     * GET /api/admin/media — multimedia management list.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Media::query()->with('category');

        $this->applySearch($request, $query, ['title', 'description']);
        $this->applyStatus($request, $query);
        $this->applyCategory($request, $query);

        if ($type = $request->input('type')) {
            $query->where('media_type', $type);
        }

        $query->orderByDesc('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();
        $map = $this->bookmarkedMap('media', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Media $media): array => ApiSerializer::media($media, $map))
        );
    }

    /**
     * POST /api/admin/media — create a media entry.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'media_type' => ['required', 'string', Rule::in(Media::TYPES)],
            'description' => ['nullable', 'string', 'max:2000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'url' => ['nullable', 'string', 'max:500'],
            'embed_url' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'string', 'max:20'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['string', 'max:500'],
            'views_count' => ['nullable', 'integer', 'min:0'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['sometimes', 'string', Rule::in([Media::STATUS_DRAFT, Media::STATUS_PUBLISHED, Media::STATUS_ARCHIVED])],
        ]);

        $data['slug'] = $this->uniqueSlug('media', $data['slug'] ?? $data['title']);
        $data['thumbnail'] = $this->uploadImage($request, 'thumbnail');
        $data['created_by'] = $request->user()->id;

        $media = Media::query()->create($data);

        return response()->json(['data' => ApiSerializer::media($media->load('category'))], 201);
    }

    /**
     * PUT/PATCH /api/admin/media/{medium} — update (bound by slug).
     */
    public function update(Request $request, Media $medium): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'media_type' => ['sometimes', 'string', Rule::in(Media::TYPES)],
            'description' => ['nullable', 'string', 'max:2000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'url' => ['nullable', 'string', 'max:500'],
            'embed_url' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'string', 'max:20'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['string', 'max:500'],
            'views_count' => ['nullable', 'integer', 'min:0'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['sometimes', 'string', Rule::in([Media::STATUS_DRAFT, Media::STATUS_PUBLISHED, Media::STATUS_ARCHIVED])],
        ]);

        if (array_key_exists('slug', $data) || array_key_exists('title', $data)) {
            $data['slug'] = $this->uniqueSlug('media', $data['slug'] ?? $data['title'], $medium->id);
        }

        $data['thumbnail'] = $this->uploadImage($request, 'thumbnail', $medium->thumbnail);

        $medium->update($data);

        return response()->json(['data' => ApiSerializer::media($medium->fresh('category'))]);
    }

    /**
     * DELETE /api/admin/media/{medium} — soft delete.
     */
    public function destroy(Media $medium): JsonResponse
    {
        $medium->delete();

        return response()->json(['message' => 'Media entry deleted.']);
    }
}
