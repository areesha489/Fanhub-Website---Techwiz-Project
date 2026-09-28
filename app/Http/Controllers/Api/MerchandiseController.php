<?php

namespace App\Http\Controllers\Api;

use App\Models\Merchandise;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchandiseController extends ApiController
{
    /**
     * GET /api/merchandise — showcase catalogue (no checkout — display only).
     *
     * Query params: q, category, fandom, upcoming, sort, per_page, page.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Merchandise::query()->published()->with('category');

        $q = trim((string) $request->input('q'));
        if ($q !== '') {
            $query->search($q);
        }

        if ($categoryId = $this->categoryId($request->input('category'))) {
            $query->where('category_id', $categoryId);
        }

        if ($fandom = $request->input('fandom')) {
            $query->where('fandom', $fandom);
        }

        if ($request->has('upcoming')) {
            $query->where('is_upcoming', $request->boolean('upcoming'));
        }

        match ($request->input('sort')) {
            'price_asc' => $query->orderByRaw('ISNULL(price), price ASC'),
            'price_desc' => $query->orderByRaw('ISNULL(price), price DESC'),
            'newest' => $query->latest('id'),
            'title' => $query->orderBy('title'),
            default => $query->orderByDesc('popularity'),
        };

        $paginator = $query->paginate($this->perPage($request))->withQueryString();
        $map = $this->bookmarkedMap('merchandise', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Merchandise $item): array => ApiSerializer::merchandise($item, $map))
        );
    }
}
