<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Content;
use App\Models\ContentView;
use App\Models\Feedback;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    /**
     * The full analytics board: totals, growth, traffic mix and top performers.
     */
    public function index(): View
    {
        $feedbackTotal = Feedback::query()->count();
        $feedbackResolved = Feedback::query()->whereIn('status', ['resolved', 'closed'])->count();
        $resolvedPercent = $feedbackTotal > 0 ? round(($feedbackResolved / $feedbackTotal) * 100, 1) : 0.0;

        $averageRating = Rating::query()->avg('value');

        $stats = [
            ['label' => 'Total views', 'value' => number_format(ContentView::query()->count()), 'icon' => 'bi-eye-fill', 'tone' => 'cyan'],
            ['label' => 'Average rating', 'value' => $averageRating === null ? '—' : number_format((float) $averageRating, 1), 'icon' => 'bi-star-fill', 'tone' => 'amber'],
            ['label' => 'Bookmarks', 'value' => number_format(Bookmark::query()->count()), 'icon' => 'bi-bookmark-fill', 'tone' => 'violet'],
            ['label' => 'Feedback resolved', 'value' => number_format($resolvedPercent, 1).'%', 'icon' => 'bi-check2-circle', 'tone' => 'green'],
        ];

        $charts = [
            $this->lineChart('chartUserGrowth', 'New users', $this->monthlySeries(User::query())),
            $this->lineChart('chartContentGrowth', 'New content', $this->monthlySeries(Content::query())),
            $this->popularCategoriesChart(),
            $this->trafficByTypeChart(),
            $this->mostViewedChart(),
            $this->feedbackStatusChart(),
        ];

        return view('admin.analytics', [
            'stats' => $stats,
            'charts' => $charts,
            'topContent' => Content::query()->with('category')->orderByDesc('views_count')->limit(10)->get(),
            'recentSignups' => User::query()->latest('id')->limit(10)->get(),
            'feedbackTotal' => $feedbackTotal,
        ]);
    }

    /**
     * Counts bucketed by month for the last 12 months (portable across MySQL/SQLite).
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    private function monthlySeries($query): array
    {
        $since = Carbon::now()->startOfMonth()->subMonths(11);

        $counts = $query
            ->where('created_at', '>=', $since)
            ->pluck('created_at')
            ->map(fn ($value) => Carbon::parse($value)->format('Y-m'))
            ->countBy();

        $keys = [];
        $labels = [];

        for ($offset = 11; $offset >= 0; $offset--) {
            $month = Carbon::now()->startOfMonth()->subMonths($offset);
            $keys[] = $month->format('Y-m');
            $labels[] = $month->format('M y');
        }

        return [
            'labels' => $labels,
            'values' => array_map(fn (string $key) => (int) ($counts[$key] ?? 0), $keys),
        ];
    }

    /**
     * Build a Chart.js line spec in the window.__CHARTS__ shape.
     *
     * @param  array{labels: list<string>, values: list<int>}  $series
     * @return array<string, mixed>
     */
    private function lineChart(string $id, string $label, array $series): array
    {
        return [
            'id' => $id,
            'config' => [
                'type' => 'line',
                'data' => [
                    'labels' => $series['labels'],
                    'datasets' => [[
                        'label' => $label,
                        'data' => $series['values'],
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.16)',
                        'fill' => true,
                        'tension' => 0.4,
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'pointBackgroundColor' => '#3B82F6',
                    ]],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => true, 'labels' => ['color' => '#94A3B8']]],
                    'scales' => [
                        'x' => ['grid' => ['color' => 'rgba(255,255,255,0.06)'], 'ticks' => ['color' => '#94A3B8']],
                        'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(255,255,255,0.06)'], 'ticks' => ['color' => '#94A3B8', 'precision' => 0]],
                    ],
                ],
            ],
        ];
    }

    /**
     * Contents per category (top 8) as a vertical bar chart.
     *
     * @return array<string, mixed>
     */
    private function popularCategoriesChart(): array
    {
        $rows = Content::query()
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $names = Category::query()->pluck('name', 'id');

        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            $labels[] = (string) ($names[$row->category_id] ?? 'Uncategorised');
            $values[] = (int) $row->total;
        }

        return [
            'id' => 'chartPopularCategories',
            'config' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => 'Content items',
                        'data' => $values,
                        'backgroundColor' => '#EC4899',
                        'borderRadius' => 6,
                    ]],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => [
                        'x' => ['grid' => ['display' => false], 'ticks' => ['color' => '#94A3B8']],
                        'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(255,255,255,0.06)'], 'ticks' => ['color' => '#94A3B8', 'precision' => 0]],
                    ],
                ],
            ],
        ];
    }

    /**
     * Recorded views grouped by the viewed model type as a doughnut chart.
     *
     * @return array<string, mixed>
     */
    private function trafficByTypeChart(): array
    {
        $rows = ContentView::query()
            ->selectRaw('viewable_type, COUNT(*) as total')
            ->groupBy('viewable_type')
            ->orderByDesc('total')
            ->get();

        return [
            'id' => 'chartTrafficByType',
            'config' => [
                'type' => 'doughnut',
                'data' => [
                    'labels' => $rows->map(fn ($row) => class_basename($row->viewable_type))->values()->all(),
                    'datasets' => [[
                        'label' => 'Views',
                        'data' => $rows->map(fn ($row) => (int) $row->total)->values()->all(),
                        'backgroundColor' => ['#8B5CF6', '#EC4899', '#22D3EE', '#3B82F6', '#34D399', '#FBBF24', '#FB7185'],
                        'borderColor' => 'rgba(13, 23, 42, 0.9)',
                        'borderWidth' => 2,
                    ]],
                ],
                'options' => [
                    'cutout' => '62%',
                    'plugins' => ['legend' => ['position' => 'right', 'labels' => ['color' => '#94A3B8']]],
                ],
            ],
        ];
    }

    /**
     * Top 6 content items by views as a horizontal bar chart.
     *
     * @return array<string, mixed>
     */
    private function mostViewedChart(): array
    {
        $rows = Content::query()
            ->orderByDesc('views_count')
            ->limit(6)
            ->get(['title', 'views_count']);

        $palette = ['#8B5CF6', '#EC4899', '#22D3EE', '#3B82F6', '#34D399', '#FBBF24'];

        return [
            'id' => 'chartMostViewed',
            'config' => [
                'type' => 'bar',
                'indexAxis' => 'y',
                'data' => [
                    'labels' => $rows->pluck('title')->all(),
                    'datasets' => [[
                        'label' => 'Views',
                        'data' => $rows->pluck('views_count')->map(fn ($v) => (int) $v)->all(),
                        'backgroundColor' => array_slice($palette, 0, max(1, $rows->count())),
                        'borderRadius' => 6,
                    ]],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => [
                        'x' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(255,255,255,0.06)'], 'ticks' => ['color' => '#94A3B8', 'precision' => 0]],
                        'y' => ['grid' => ['display' => false], 'ticks' => ['color' => '#94A3B8']],
                    ],
                ],
            ],
        ];
    }

    /**
     * Feedback split by status as a doughnut chart.
     *
     * @return array<string, mixed>
     */
    private function feedbackStatusChart(): array
    {
        $statuses = ['open', 'in_review', 'resolved', 'closed'];

        $counts = Feedback::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        $labels = ['Open', 'In review', 'Resolved', 'Closed'];
        $values = array_map(fn (string $status) => (int) ($counts[$status] ?? 0), $statuses);

        return [
            'id' => 'chartFeedbackStatus',
            'config' => [
                'type' => 'doughnut',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => 'Feedback',
                        'data' => $values,
                        'backgroundColor' => ['#FB7185', '#FBBF24', '#34D399', '#94A3C4'],
                        'borderColor' => 'rgba(13, 23, 42, 0.9)',
                        'borderWidth' => 2,
                    ]],
                ],
                'options' => [
                    'cutout' => '62%',
                    'plugins' => ['legend' => ['position' => 'right', 'labels' => ['color' => '#94A3B8']]],
                ],
            ],
        ];
    }
}
