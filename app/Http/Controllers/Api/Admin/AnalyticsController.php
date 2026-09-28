<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Content;
use App\Models\ContentView;
use App\Models\FanSubmission;
use App\Models\Rating;
use App\Models\User;
use App\Models\UserPreference;
use App\Support\BookmarkHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsController extends AdminApiController
{
    /**
     * GET /api/admin/analytics — chart series for the analytics screens.
     */
    public function index(Request $request): JsonResponse
    {
        $days = min(180, max(7, (int) $request->input('days', 30)));
        $start = now()->startOfDay()->subDays($days - 1);

        $payload = [
            'range' => [
                'days' => $days,
                'from' => $start->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
            'views_series' => $this->dailySeries(ContentView::class, $start),
            'registrations_series' => $this->dailySeries(User::class, $start),
            'bookmarks_series' => $this->dailySeries(Bookmark::class, $start),
            'submissions_series' => $this->dailySeries(FanSubmission::class, $start),
            'content_by_category' => $this->groupWithNames(Content::class, 'category_id'),
            'bookmarks_by_type' => $this->bookmarksByType(),
            'ratings_distribution' => $this->ratingsDistribution(),
            'submissions_by_status' => $this->countBy(FanSubmission::class, 'status'),
            'top_contents' => Content::query()
                ->orderByDesc('views_count')
                ->take(10)
                ->get(['id', 'title', 'views_count', 'rating', 'popularity'])
                ->map(fn (Content $content): array => [
                    'id' => $content->id,
                    'title' => $content->title,
                    'views_count' => (int) $content->views_count,
                    'rating' => (float) $content->rating,
                    'popularity' => (int) $content->popularity,
                ])
                ->values()
                ->all(),
            'preferred_theme' => $this->countBy(UserPreference::class, 'theme'),
            'total_users' => User::query()->count(),
            'verified_users' => User::query()->whereNotNull('email_verified_at')->count(),
        ];

        return response()->json($this->data($payload));
    }

    /**
     * Daily counts for a model's created_at over the window, gap-filled.
     *
     * @param  class-string<Model>  $model
     * @return list<array{date: string, count: int}>
     */
    private function dailySeries(string $model, \DateTimeInterface $start): array
    {
        $rows = $model::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $series = [];
        $cursor = Carbon::instance($start)->startOfDay();
        $end = now()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $series[] = ['date' => $key, 'count' => (int) ($rows[$key] ?? 0)];
            $cursor->addDay();
        }

        return $series;
    }

    /**
     * Count rows grouped by a column, keyed by the column value.
     *
     * @param  class-string<Model>  $model
     * @return list<array{value: string, count: int}>
     */
    private function countBy(string $model, string $column): array
    {
        return $model::query()
            ->selectRaw("{$column} as value, COUNT(*) as count")
            ->groupBy($column)
            ->orderBy($column)
            ->get()
            ->map(fn ($row): array => [
                'value' => (string) $row->value,
                'count' => (int) $row->count,
            ])
            ->values()
            ->all();
    }

    /**
     * Counts per category with the category name resolved in one query.
     *
     * @return list<array{id: int, name: string, count: int}>
     */
    private function groupWithNames(string $model, string $column): array
    {
        $names = Category::query()->pluck('name', 'id');

        return $model::query()
            ->selectRaw("{$column} as group_id, COUNT(*) as count")
            ->groupBy($column)
            ->orderBy($column)
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->group_id,
                'name' => (string) ($names[$row->group_id] ?? 'Uncategorised'),
                'count' => (int) $row->count,
            ])
            ->values()
            ->all();
    }

    /**
     * Bookmark counts per public type slug.
     *
     * @return list<array{value: string, count: int}>
     */
    private function bookmarksByType(): array
    {
        $reverse = BookmarkHelper::reverseMap();

        return Bookmark::query()
            ->selectRaw('bookmarkable_type as value, COUNT(*) as count')
            ->groupBy('bookmarkable_type')
            ->get()
            ->map(fn ($row): array => [
                'value' => $reverse[$row->value] ?? 'unknown',
                'count' => (int) $row->count,
            ])
            ->sortBy('value')
            ->values()
            ->all();
    }

    /**
     * Ratings grouped by star value (1..10).
     *
     * @return list<array{value: int, count: int}>
     */
    private function ratingsDistribution(): array
    {
        $rows = Rating::query()
            ->selectRaw('value, COUNT(*) as count')
            ->groupBy('value')
            ->pluck('count', 'value');

        $distribution = [];

        for ($value = Rating::MIN_VALUE; $value <= Rating::MAX_VALUE; $value++) {
            $distribution[] = ['value' => $value, 'count' => (int) ($rows[$value] ?? 0)];
        }

        return $distribution;
    }
}
