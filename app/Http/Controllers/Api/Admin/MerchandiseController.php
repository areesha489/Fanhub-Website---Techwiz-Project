<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Merchandise;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MerchandiseController extends AdminApiController
{
    /**
     * GET /api/admin/merchandise — showcase product management list.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Merchandise::query()->with('category');

        $this->applySearch($request, $query, ['title', 'fandom', 'description']);
        $this->applyStatus($request, $query);
        $this->applyCategory($request, $query);

        if ($request->has('upcoming')) {
            $query->where('is_upcoming', $request->boolean('upcoming'));
        }

        $query->orderByDesc('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();
        $map = $this->bookmarkedMap('merchandise', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Merchandise $item): array => ApiSerializer::merchandise($item, $map))
        );
    }

    /**
     * POST /api/admin/merchandise — create a showcase product.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug('merchandise', $data['slug'] ?? $data['title']);
        $data['image'] = $this->uploadImage($request, 'image');
        $data['status'] = $data['status'] ?? Merchandise::STATUS_PUBLISHED;

        $item = Merchandise::query()->create($data);

        return response()->json(['data' => ApiSerializer::merchandise($item->load('category'))], 201);
    }

    /**
     * PUT/PATCH /api/admin/merchandise/{merchandise} — update (bound by slug).
     */
    public function update(Request $request, Merchandise $merchandise): JsonResponse
    {
        $data = $this->validated($request, partial: true);

        if (array_key_exists('slug', $data) || array_key_exists('title', $data)) {
            $data['slug'] = $this->uniqueSlug('merchandise', $data['slug'] ?? $data['title'], $merchandise->id);
        }

        $data['image'] = $this->uploadImage($request, 'image', $merchandise->image);

        $merchandise->update($data);

        return response()->json(['data' => ApiSerializer::merchandise($merchandise->fresh('category'))]);
    }

    /**
     * DELETE /api/admin/merchandise/{merchandise} — soft delete.
     */
    public function destroy(Merchandise $merchandise): JsonResponse
    {
        $merchandise->delete();

        return response()->json(['message' => 'Product deleted.']);
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
            'category_id' => [$required, 'integer', 'exists:categories,id'],
            'fandom' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'currency' => ['nullable', 'string', 'max:6'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'is_upcoming' => ['nullable', 'boolean'],
            'release_date' => ['nullable', 'date'],
            'popularity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['sometimes', 'string', Rule::in([Merchandise::STATUS_DRAFT, Merchandise::STATUS_PUBLISHED, Merchandise::STATUS_ARCHIVED])],
        ]);
    }
}
