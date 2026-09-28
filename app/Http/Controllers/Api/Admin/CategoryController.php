<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Category;
use App\Models\Content;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends AdminApiController
{
    /**
     * GET /api/admin/categories — category list with published content counts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        $this->applySearch($request, $query, ['name', 'description']);
        $query->orderBy('position')->orderBy('name');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();
        $counts = $this->contentCounts();

        return response()->json(
            ApiSerializer::page($paginator, fn (Category $category): array => ApiSerializer::category(
                $category,
                (int) ($counts[$category->id] ?? 0)
            ))
        );
    }

    /**
     * POST /api/admin/categories — create a category.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug('categories', $data['slug'] ?? $data['name']);
        $data['image'] = $this->uploadImage($request, 'image');

        $category = Category::query()->create($data);

        return response()->json(['data' => ApiSerializer::category($category)], 201);
    }

    /**
     * PUT/PATCH /api/admin/categories/{category} — update a category.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $this->validated($request);

        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug(
                'categories',
                $data['slug'] ?? $data['name'],
                $category->id
            );
        }

        $data['image'] = $this->uploadImage($request, 'image', $category->image);

        $category->update($data);

        return response()->json(['data' => ApiSerializer::category($category->refresh())]);
    }

    /**
     * DELETE /api/admin/categories/{category} — remove a category (cascades).
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Category deleted.']);
    }

    /**
     * Shared validation for store/update.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'accent' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    /**
     * Published content counts keyed by category id.
     *
     * @return array<int, int>
     */
    private function contentCounts(): array
    {
        return Content::query()
            ->published()
            ->groupBy('category_id')
            ->selectRaw('category_id, COUNT(*) as total')
            ->pluck('total', 'category_id')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }
}
