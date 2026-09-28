<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Support\BookmarkHelper;
use App\Support\Engagement;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * Cards rendered per calendar page.
     */
    private const PER_PAGE = 12;

    /**
     * Sort keys accepted by the calendar.
     *
     * @var list<string>
     */
    private const SORTS = ['date', 'popular', 'latest'];

    /**
     * Event calendar: city, category, type and status filters with sorting.
     */
    public function index(Request $request): View
    {
        $city = (string) $request->query('city', '');
        $category = (string) $request->query('category', '');
        $type = (string) $request->query('type', '');
        $status = (string) $request->query('status', '');
        $requestedSort = (string) $request->query('sort', 'date');
        $sort = in_array($requestedSort, self::SORTS, true) ? $requestedSort : 'date';

        $categories = collect();
        $cities = collect();
        $eventTypes = collect();
        $statuses = collect();
        $events = null;
        $totalUpcoming = 0;
        $error = null;

        try {
            $categories = Category::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'accent']);

            $cities = Event::query()
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->distinct()
                ->orderBy('city')
                ->pluck('city');

            $eventTypes = Event::query()
                ->whereNotNull('event_type')
                ->where('event_type', '!=', '')
                ->distinct()
                ->orderBy('event_type')
                ->pluck('event_type');

            $statuses = Event::query()
                ->whereNotNull('status')
                ->distinct()
                ->orderBy('status')
                ->pluck('status');

            $totalUpcoming = Event::query()->where('status', Event::STATUS_UPCOMING)->count();

            $query = Event::query()->with('category');

            if ($city !== '') {
                $query->where('city', $city);
            }

            if ($category !== '') {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $category));
            }

            if ($type !== '') {
                $query->where('event_type', $type);
            }

            if ($status !== '') {
                $query->where('status', $status);
            }

            match ($sort) {
                'popular' => $query->orderByDesc('views_count')->orderByDesc('id'),
                'latest' => $query->orderByDesc('created_at')->orderByDesc('id'),
                default => $query->orderBy('start_date')->orderBy('end_date'),
            };

            $events = $query->paginate(self::PER_PAGE)->withQueryString();
            $this->applyBookmarks($events, 'event');
        } catch (\Throwable $exception) {
            Log::warning('events.index failed: '.$exception->getMessage());
            $error = 'We could not load the event calendar right now. Please refresh the page or try again in a moment.';
        }

        return view('events.index', [
            'events' => $events,
            'categories' => $categories,
            'cities' => $cities,
            'eventTypes' => $eventTypes,
            'statuses' => $statuses,
            'city' => $city,
            'category' => $category,
            'type' => $type,
            'status' => $status,
            'sort' => $sort,
            'totalUpcoming' => $totalUpcoming,
            'total' => $events?->total() ?? 0,
            'error' => $error,
        ]);
    }

    /**
     * Event detail: schedule, venue, map link, tickets and related events.
     */
    public function show(string $slug): View
    {
        try {
            $event = Event::query()
                ->with(['category'])
                ->where('slug', $slug)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            abort(404);
        } catch (\Throwable $exception) {
            Log::warning('events.show failed: '.$exception->getMessage());

            return view('events.show', [
                'event' => null,
                'loadError' => 'We could not load this event right now. Please try again in a moment.',
            ]);
        }

        Engagement::recordView($event, 'event');

        $related = collect();
        $isBookmarked = false;
        $bookmarkId = null;

        try {
            $related = Event::query()
                ->with(['category'])
                ->where('category_id', $event->category_id)
                ->where('id', '!=', $event->id)
                ->orderByDesc('start_date')
                ->limit(3)
                ->get();

            $bookmarkId = BookmarkHelper::bookmarkIdFor('event', (int) $event->id);
            $isBookmarked = $bookmarkId !== null;
        } catch (\Throwable $exception) {
            Log::warning('events.show extras failed: '.$exception->getMessage());
        }

        return view('events.show', [
            'event' => $event,
            'related' => $related,
            'isBookmarked' => $isBookmarked,
            'bookmarkId' => $bookmarkId,
            'loadError' => null,
        ]);
    }

    /**
     * Preload bookmark state for a page of cards in a single query.
     *
     * @param  LengthAwarePaginator<int, Event>  $paginator
     */
    private function applyBookmarks(LengthAwarePaginator $paginator, string $type): void
    {
        $map = BookmarkHelper::bookmarkedMap(
            $type,
            $paginator->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all()
        );

        foreach ($paginator->getCollection() as $item) {
            $item->bookmarked = array_key_exists((int) $item->id, $map);
            $item->bookmark_id = $map[(int) $item->id] ?? null;
        }
    }
}
