@extends('layouts.admin')

@section('title', 'Analytics · Fan Hub+ Admin')

@section('styles')
<style>
    .fh-admin-stats {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .fh-admin-charts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }
    .fh-chart-box {
        position: relative;
        height: 340px;
        overflow: hidden;
    }
    .fh-admin-subgrid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.25rem;
    }
    @media (min-width: 1600px) {
        .fh-admin-subgrid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endsection

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Analytics</h1>
            <p class="fh-page-sub">
                Aggregated from the database in real time — traffic, ratings, bookmarks and moderation outcomes.
            </p>
        </div>
        <a class="btn btn-fh-outline" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-grid-1x2-fill me-2" aria-hidden="true"></i>Dashboard
        </a>
    </div>

    <div class="fh-admin-stats">
        @foreach($stats as $stat)
            <x-stat-card
                :label="$stat['label']"
                :value="$stat['value']"
                :icon="$stat['icon']"
                :tone="$stat['tone']"
            />
        @endforeach
    </div>

    <div class="fh-admin-charts">
        <section class="fh-card fh-card-pad" aria-labelledby="chartUserGrowthTitle">
            <h2 class="fh-card-title" id="chartUserGrowthTitle">User growth</h2>
            <p class="fh-sub mb-3">New accounts per month, last 12 months.</p>
            <div class="fh-chart-box"><canvas id="chartUserGrowth" role="img" aria-label="Line chart of new users per month"></canvas></div>
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="chartContentGrowthTitle">
            <h2 class="fh-card-title" id="chartContentGrowthTitle">Content growth</h2>
            <p class="fh-sub mb-3">Catalogue additions per month, last 12 months.</p>
            <div class="fh-chart-box"><canvas id="chartContentGrowth" role="img" aria-label="Line chart of new content per month"></canvas></div>
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="chartPopularCategoriesTitle">
            <h2 class="fh-card-title" id="chartPopularCategoriesTitle">Popular categories</h2>
            <p class="fh-sub mb-3">Content items grouped by fandom bucket.</p>
            <div class="fh-chart-box"><canvas id="chartPopularCategories" role="img" aria-label="Bar chart of content per category"></canvas></div>
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="chartTrafficByTypeTitle">
            <h2 class="fh-card-title" id="chartTrafficByTypeTitle">Traffic by type</h2>
            <p class="fh-sub mb-3">Recorded page views grouped by the viewed model.</p>
            <div class="fh-chart-box"><canvas id="chartTrafficByType" role="img" aria-label="Doughnut chart of views by content type"></canvas></div>
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="chartMostViewedTitle">
            <h2 class="fh-card-title" id="chartMostViewedTitle">Most viewed</h2>
            <p class="fh-sub mb-3">Top 6 titles by lifetime views.</p>
            <div class="fh-chart-box"><canvas id="chartMostViewed" role="img" aria-label="Horizontal bar chart of the most viewed titles"></canvas></div>
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="chartFeedbackStatusTitle">
            <h2 class="fh-card-title" id="chartFeedbackStatusTitle">Feedback status</h2>
            <p class="fh-sub mb-3">{{ number_format($feedbackTotal) }} feedback entries by state.</p>
            <div class="fh-chart-box"><canvas id="chartFeedbackStatus" role="img" aria-label="Doughnut chart of feedback by status"></canvas></div>
        </section>
    </div>

    <div class="fh-admin-subgrid">
        <section class="fh-card fh-card-pad" aria-labelledby="topContentTitle">
            <h2 class="fh-card-title mb-3" id="topContentTitle">Top 10 content by views</h2>

            @if($topContent->isEmpty())
                <x-empty title="No content yet" message="Once titles are added they will be ranked here." icon="bi-bar-chart" />
            @else
                @php
                    $topContentColumns = [
                        ['key' => 'title', 'label' => 'Title'],
                        ['key' => 'category.name', 'label' => 'Category'],
                        ['key' => 'views_count', 'label' => 'Views', 'format' => fn ($row) => number_format((int) $row->views_count)],
                        ['key' => 'rating', 'label' => 'Rating', 'format' => fn ($row) => e(number_format((float) $row->rating, 1)).' / 10'],
                        ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                    ];
                @endphp

                <x-admin.table :items="$topContent" :columns="$topContentColumns" />
            @endif
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="recentSignupsTitle">
            <h2 class="fh-card-title mb-3" id="recentSignupsTitle">Recent signups</h2>

            @if($recentSignups->isEmpty())
                <x-empty title="No members yet" message="New registrations will be listed here." icon="bi-people" />
            @else
                @php
                    $recentSignupColumns = [
                        ['key' => 'name', 'label' => 'Member'],
                        ['key' => 'email', 'label' => 'Email'],
                        ['key' => 'role', 'label' => 'Role', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->role])->render()],
                        ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                        ['key' => 'created_at', 'label' => 'Joined', 'format' => fn ($row) => e($row->created_at?->format('d M Y'))],
                    ];
                @endphp

                <x-admin.table :items="$recentSignups" :columns="$recentSignupColumns" />
            @endif
        </section>
    </div>
@endsection

@section('scripts')
<script>
    window.__CHARTS__ = @json($charts);

    if (document.readyState !== 'loading' && window.FanHub && FanHub.renderCharts && (!FanHub.charts || FanHub.charts.length === 0)) {
        FanHub.renderCharts();
    }
</script>
@endsection
