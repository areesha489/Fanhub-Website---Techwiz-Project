<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Character;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CharacterController extends AdminApiController
{
    /**
     * GET /api/admin/characters — character management list.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Character::query()->with(['category', 'contents']);

        $this->applySearch($request, $query, ['name', 'fandom', 'bio']);
        $this->applyCategory($request, $query);

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        $query->orderByDesc('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();
        $map = $this->bookmarkedMap('character', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Character $character): array => ApiSerializer::character($character, $map))
        );
    }

    /**
     * POST /api/admin/characters — create a character.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'fandom' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'gender' => ['nullable', 'string', 'max:50'],
            'age' => ['nullable', 'string', 'max:50'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'abilities' => ['nullable', 'string', 'max:500'],
            'debut' => ['nullable', 'string', 'max:50'],
            'views_count' => ['nullable', 'integer', 'min:0'],
            'popularity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['string', 'max:500'],
        ]);

        $data['slug'] = $this->uniqueSlug('characters', $data['slug'] ?? $data['name']);
        $data['image'] = $this->uploadImage($request, 'image');

        $character = Character::query()->create($data);

        return response()->json(['data' => ApiSerializer::character($character->load('category'))], 201);
    }

    /**
     * PUT/PATCH /api/admin/characters/{character} — update (bound by slug).
     */
    public function update(Request $request, Character $character): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'fandom' => ['sometimes', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'gender' => ['nullable', 'string', 'max:50'],
            'age' => ['nullable', 'string', 'max:50'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'abilities' => ['nullable', 'string', 'max:500'],
            'debut' => ['nullable', 'string', 'max:50'],
            'views_count' => ['nullable', 'integer', 'min:0'],
            'popularity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['string', 'max:500'],
        ]);

        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug('characters', $data['slug'] ?? $data['name'], $character->id);
        }

        $data['image'] = $this->uploadImage($request, 'image', $character->image);

        $character->update($data);

        return response()->json(['data' => ApiSerializer::character($character->fresh('category'))]);
    }

    /**
     * DELETE /api/admin/characters/{character} — soft delete.
     */
    public function destroy(Character $character): JsonResponse
    {
        $character->delete();

        return response()->json(['message' => 'Character deleted.']);
    }
}
