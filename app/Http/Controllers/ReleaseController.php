<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Support\BookmarkHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ReleaseController extends Controller
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
    private const SORTS = ['date', 'popular', 'alphabetical'];

    /**
     * Upcoming releases: type/status filters, sorting and a "this month" rail.
     */
    public function index(Request $request): View
    {
        $type = (string) $request->query('type', '');
        $status = (string) $request->query('status', '');
        $requestedSort = (string) $request->query('sort', 'date');
        $sort = in_array($requestedSort, self::SORTS, true) ? $requestedSort : 'date';

        $releases = null;
        $thisMonth = collect();
        $error = null;

        try {
            $query = Release::query()->with(['category', 'content']);

            if ($type !== '' && in_array($type, Release::TYPES, true)) {
                $query->where('release_type', $type);
            }

            if ($status !== '' && in_array($status, [Release::STATUS_UPCOMING, Release::STATUS_RELEASED, Release::STATUS_DELAYED], true)) {
                $query->where('status', $status);
            }

            match ($sort) {
                'popular' => $query->orderByDesc('popularity')->orderByDesc('id'),
                'alphabetical' => $query->orderBy('title'),
                default => $query->orderBy('release_date')->orderByDesc('id'),
            };

            $releases = $query->paginate(self::PER_PAGE)->withQueryString();
            $this->applyBookmarks($releases, 'release');

            $thisMonth = Release::query()
                ->with(['category', 'content'])
                ->whereBetween('release_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->orderBy('release_date')
                ->limit(5)
                ->get();
        } catch (\Throwable $exception) {
            Log::warning('releases.index failed: '.$exception->getMessage());
            $error = 'We could not load the release calendar right now. Please refresh the page or try again in a moment.';
        }

        return view('releases.index', [
            'releases' => $releases,
            'thisMonth' => $thisMonth,
            'type' => $type,
            'status' => $status,
            'sort' => $sort,
            'types' => Release::TYPES,
            'total' => $releases?->total() ?? 0,
            'error' => $error,
        ]);
    }

    /**
     * Preload bookmark state for a page of cards in a single query.
     *
     * @param  LengthAwarePaginator<int, Release>  $paginator
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
