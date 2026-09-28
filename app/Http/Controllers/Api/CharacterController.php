<?php

namespace App\Http\Controllers\Api;

use App\Models\Character;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CharacterController extends ApiController
{
    /**
     * GET /api/characters — filterable, paginated character index.
     *
     * Query params: q, category (slug|id), featured, sort, per_page, page.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Character::query()->with('category');

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
            'newest' => $query->latest('id'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('popularity'),
        };

        $paginator = $query->paginate($this->perPage($request))->withQueryString();
        $map = $this->bookmarkedMap('character', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Character $character): array => ApiSerializer::character($character, $map))
        );
    }

    /**
     * GET /api/characters/{slug} — character detail with relations and stats.
     */
    public function show(string $slug): JsonResponse
    {
        $character = Character::query()
            ->with([
                'category',
                'contents.category',
                'contents.tags',
                'relatedCharacters.category',
                'relatedToCharacters.category',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $map = $this->bookmarkedMap('character', [$character]);

        return response()->json($this->data(ApiSerializer::character($character, $map, detail: true)));
    }
}
