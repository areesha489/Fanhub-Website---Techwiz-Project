<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends ApiController
{
    /**
     * GET /api/events — filterable event calendar, upcoming first.
     *
     * Query params: q, type, city, status, category, featured, per_page, page.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::query()->with('category');

        $q = trim((string) $request->input('q'));
        if ($q !== '') {
            $query->search($q);
        }

        if ($type = $request->input('type')) {
            $query->where('event_type', $type);
        }

        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        if ($status = $request->input('status')) {
            $query->status($status);
        }

        if ($categoryId = $this->categoryId($request->input('category'))) {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        $query->orderByRaw("CASE WHEN status IN ('upcoming', 'ongoing') THEN 0 ELSE 1 END")
            ->orderBy('start_date');

        $paginator = $query->paginate($this->perPage($request))->withQueryString();
        $map = $this->bookmarkedMap('event', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Event $event): array => ApiSerializer::event($event, $map))
        );
    }

    /**
     * GET /api/events/{slug} — event detail with related events.
     */
    public function show(string $slug): JsonResponse
    {
        $event = Event::query()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        $map = $this->bookmarkedMap('event', [$event]);

        return response()->json($this->data(ApiSerializer::event($event, $map, detail: true)));
    }
}
