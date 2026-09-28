<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Character;
use App\Models\ChatbotQuery;
use App\Models\Content;
use App\Models\Event;
use App\Models\FanSubmission;
use App\Models\Feedback;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Release;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Admin home: platform totals, growth charts and the latest activity.
     */
    public function index(): View
    {
        $stats = [
            ['label' => 'Total users', 'value' => User::query()->count(), 'icon' => 'bi-people-fill', 'tone' => 'violet'],
            ['label' => 'Active users', 'value' => User::query()->where('status', 'active')->count(), 'icon' => 'bi-person-check-fill', 'tone' => 'green'],
            ['label' => 'Total content', 'value' => Content::query()->count(), 'icon' => 'bi-film', 'tone' => 'cyan'],
            ['label' => 'Articles', 'value' => Article::query()->count(), 'icon' => 'bi-newspaper', 'tone' => 'blue'],
            ['label' => 'Characters', 'value' => Character::query()->count(), 'icon' => 'bi-people', 'tone' => 'pink'],
            ['label' => 'Media', 'value' => Media::query()->count(), 'icon' => 'bi-play-circle-fill', 'tone' => 'amber'],
            ['label' => 'Events', 'value' => Event::query()->count(), 'icon' => 'bi-calendar-event-fill', 'tone' => 'coral'],
            ['label' => 'Bookmarks', 'value' => Bookmark::query()->count(), 'icon' => 'bi-bookmark-fill', 'tone' => 'slate'],
            ['label' => 'Merchandise', 'value' => Merchandise::query()->count(), 'icon' => 'bi-bag-fill', 'tone' => 'pink'],
            ['label' => 'Releases', 'value' => Release::query()->count(), 'icon' => 'bi-box-seam-fill', 'tone' => 'cyan'],
            ['label' => 'Pending submissions', 'value' => FanSubmission::query()->where('status', 'pending')->count(), 'icon' => 'bi-inbox-fill', 'tone' => 'amber'],
            ['label' => 'Open feedback', 'value' => Feedback::query()->where('status', 'open')->count(), 'icon' => 'bi-chat-quote-fill', 'tone' => 'red'],
        ];

        $charts = [
            $this->lineChart('chartUserGrowth', 'New users', $this->monthlySeries(User::query())),
            $this->lineChart('chartContentGrowth', 'New content', $this->monthlySeries(Content::query())),
            $this->popularCategoriesChart(),
            $this->mostViewedChart(),
            $this->mostBookmarkedChart(),
            $this->lineChart('chartChatbotInteractions', 'Questions asked', $this->monthlySeries(ChatbotQuery::query())),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'charts' => $charts,
            'recentUsers' => User::query()->latest('id')->limit(5)->get(),
            'recentContent' => Content::query()->with('category')->latest('id')->limit(5)->get(),
            'recentFeedback' => Feedback::query()->with('user')->latest('id')->limit(5)->get(),
            'pendingSubmissions' => FanSubmission::query()->where('status', 'pending')->count(),
            'openFeedback' => Feedback::query()->where('status', 'open')->count(),
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
                        'borderColor' => '#8B5CF6',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.16)',
                        'fill' => true,
                        'tension' => 0.4,
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'pointBackgroundColor' => '#8B5CF6',
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
                        'backgroundColor' => '#22D3EE',
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
                        'backgroundColor' => array_values(array_slice($palette, 0, max(1, $rows->count()))),
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
     * Bookmarks grouped by bookmarkable type (top 6) as a bar chart.
     *
     * @return array<string, mixed>
     */
    private function mostBookmarkedChart(): array
    {
        $rows = Bookmark::query()
            ->selectRaw('bookmarkable_type, COUNT(*) as total')
            ->groupBy('bookmarkable_type')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $labels = $rows->map(fn ($row) => class_basename($row->bookmarkable_type))->all();
        $values = $rows->map(fn ($row) => (int) $row->total)->all();

        $palette = ['#8B5CF6', '#EC4899', '#22D3EE', '#3B82F6', '#34D399', '#FBBF24'];

        return [
            'id' => 'chartMostBookmarked',
            'config' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => 'Bookmarks',
                        'data' => $values,
                        'backgroundColor' => array_slice($palette, 0, max(1, count($values))),
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
}
