<?php

namespace App\Http\Controllers\Api;

use App\Models\Release;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReleaseController extends ApiController
{
    /**
     * GET /api/releases — release calendar, upcoming first.
     *
     * Query params: type, status, q, category, per_page, page.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Release::query()->with('category');

        if ($type = $request->input('type')) {
            $query->where('release_type', $type);
        }

        if ($status = $request->input('status')) {
            $query->status($status);
        }

        $q = trim((string) $request->input('q'));
        if ($q !== '') {
            $query->search($q);
        }

        if ($categoryId = $this->categoryId($request->input('category'))) {
            $query->where('category_id', $categoryId);
        }

        $query->orderByRaw("CASE WHEN status = 'upcoming' THEN 0 ELSE 1 END")->orderBy('release_date');

        $paginator = $query->paginate($this->perPage($request))->withQueryString();
        $map = $this->bookmarkedMap('release', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Release $release): array => ApiSerializer::release($release, $map))
        );
    }
}
