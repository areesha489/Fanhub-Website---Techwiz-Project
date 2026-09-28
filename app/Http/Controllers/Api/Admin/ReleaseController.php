<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Release;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReleaseController extends AdminApiController
{
    /**
     * GET /api/admin/releases — release calendar management list.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Release::query()->with(['category', 'content']);

        $this->applySearch($request, $query, ['title', 'description']);
        $this->applyStatus($request, $query);
        $this->applyCategory($request, $query);

        if ($type = $request->input('type')) {
            $query->where('release_type', $type);
        }

        $query->orderBy('release_date');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();
        $map = $this->bookmarkedMap('release', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Release $release): array => ApiSerializer::release($release, $map))
        );
    }

    /**
     * POST /api/admin/releases — create a release entry.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug('releases', $data['slug'] ?? $data['title']);
        $data['poster'] = $this->uploadImage($request, 'poster');
        $data['status'] = $data['status'] ?? Release::STATUS_UPCOMING;

        $release = Release::query()->create($data);

        return response()->json(['data' => ApiSerializer::release($release->load('category'))], 201);
    }

    /**
     * PUT/PATCH /api/admin/releases/{release} — update (bound by slug).
     */
    public function update(Request $request, Release $release): JsonResponse
    {
        $data = $this->validated($request, partial: true);

        if (array_key_exists('slug', $data) || array_key_exists('title', $data)) {
            $data['slug'] = $this->uniqueSlug('releases', $data['slug'] ?? $data['title'], $release->id);
        }

        $data['poster'] = $this->uploadImage($request, 'poster', $release->poster);

        $release->update($data);

        return response()->json(['data' => ApiSerializer::release($release->fresh('category'))]);
    }

    /**
     * DELETE /api/admin/releases/{release} — soft delete.
     */
    public function destroy(Release $release): JsonResponse
    {
        $release->delete();

        return response()->json(['message' => 'Release deleted.']);
    }

    /**
     * Shared validation for store/update.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'title' => [$required, 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'release_type' => [$required, 'string', Rule::in(Release::TYPES)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'release_date' => [$required, 'date'],
            'description' => ['nullable', 'string', 'max:3000'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['sometimes', 'string', Rule::in([Release::STATUS_UPCOMING, Release::STATUS_RELEASED, Release::STATUS_DELAYED])],
            'content_id' => ['nullable', 'integer', 'exists:contents,id'],
            'popularity' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
    }
}
