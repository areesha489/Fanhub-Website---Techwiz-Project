<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Event;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends AdminApiController
{
    /**
     * GET /api/admin/events — event management list.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::query()->with('category');

        $this->applySearch($request, $query, ['name', 'venue', 'city']);
        $this->applyStatus($request, $query);
        $this->applyCategory($request, $query);

        if ($type = $request->input('type')) {
            $query->where('event_type', $type);
        }

        $query->orderByDesc('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();
        $map = $this->bookmarkedMap('event', $paginator->getCollection());

        return response()->json(
            ApiSerializer::page($paginator, fn (Event $event): array => ApiSerializer::event($event, $map))
        );
    }

    /**
     * POST /api/admin/events — create an event.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug('events', $data['slug'] ?? $data['name']);
        $data['cover_image'] = $this->uploadImage($request, 'cover_image');
        $data['status'] = $data['status'] ?? Event::STATUS_UPCOMING;

        $event = Event::query()->create($data);

        return response()->json(['data' => ApiSerializer::event($event->load('category'))], 201);
    }

    /**
     * PUT/PATCH /api/admin/events/{event} — update (bound by slug).
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        $data = $this->validated($request, partial: true);

        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug('events', $data['slug'] ?? $data['name'], $event->id);
        }

        $data['cover_image'] = $this->uploadImage($request, 'cover_image', $event->cover_image);

        $event->update($data);

        return response()->json(['data' => ApiSerializer::event($event->fresh('category'))]);
    }

    /**
     * DELETE /api/admin/events/{event} — soft delete.
     */
    public function destroy(Event $event): JsonResponse
    {
        $event->delete();

        return response()->json(['message' => 'Event deleted.']);
    }

    /**
     * Shared validation for store/update.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        $endRules = ['nullable', 'date'];
        if ($request->filled('start_date')) {
            $endRules[] = 'after_or_equal:start_date';
        }

        return $request->validate([
            'name' => [$required, 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category_id' => [$required, 'integer', 'exists:categories,id'],
            'event_type' => [$required, 'string', Rule::in(Event::TYPES)],
            'description' => ['nullable', 'string', 'max:3000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'start_date' => [$required, 'date'],
            'end_date' => $endRules,
            'time' => ['nullable', 'string', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/', 'max:8'],
            'venue' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['nullable', 'numeric', 'min:-180', 'max:180'],
            'ticket_url' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'string', Rule::in([Event::STATUS_UPCOMING, Event::STATUS_ONGOING, Event::STATUS_COMPLETED, Event::STATUS_CANCELLED])],
            'featured' => ['nullable', 'boolean'],
            'views_count' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
