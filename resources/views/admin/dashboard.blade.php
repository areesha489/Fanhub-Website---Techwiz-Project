@extends('layouts.admin')

@section('title', 'Dashboard · Fan Hub+ Admin')

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
            <h1 class="fh-page-title">Dashboard</h1>
            <p class="fh-page-sub">
                Platform totals, growth curves and the newest activity across Fan Hub+.
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-fh-outline" href="{{ route('admin.analytics') }}">
                <i class="bi bi-graph-up-arrow me-2" aria-hidden="true"></i>Analytics
            </a>
            <a class="btn btn-fh" href="{{ route('admin.content.create') }}">
                <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>New content
            </a>
        </div>
    </div>

    <div class="fh-admin-stats">
        @foreach($stats as $stat)
            <x-stat-card
                :label="$stat['label']"
                :value="number_format($stat['value'])"
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
            <p class="fh-sub mb-3">Titles added per month, last 12 months.</p>
            <div class="fh-chart-box"><canvas id="chartContentGrowth" role="img" aria-label="Line chart of new content per month"></canvas></div>
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="chartPopularCategoriesTitle">
            <h2 class="fh-card-title" id="chartPopularCategoriesTitle">Popular categories</h2>
            <p class="fh-sub mb-3">Catalogue items per fandom bucket.</p>
            <div class="fh-chart-box"><canvas id="chartPopularCategories" role="img" aria-label="Bar chart of content per category"></canvas></div>
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="chartMostViewedTitle">
            <h2 class="fh-card-title" id="chartMostViewedTitle">Most viewed</h2>
            <p class="fh-sub mb-3">Top 6 titles by lifetime views.</p>
            <div class="fh-chart-box"><canvas id="chartMostViewed" role="img" aria-label="Horizontal bar chart of the most viewed titles"></canvas></div>
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="chartMostBookmarkedTitle">
            <h2 class="fh-card-title" id="chartMostBookmarkedTitle">Most bookmarked</h2>
            <p class="fh-sub mb-3">Saves grouped by content type.</p>
            <div class="fh-chart-box"><canvas id="chartMostBookmarked" role="img" aria-label="Bar chart of bookmarks by content type"></canvas></div>
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="chartChatbotTitle">
            <h2 class="fh-card-title" id="chartChatbotTitle">Chatbot interactions</h2>
            <p class="fh-sub mb-3">Questions asked per month, last 12 months.</p>
            <div class="fh-chart-box"><canvas id="chartChatbotInteractions" role="img" aria-label="Line chart of chatbot questions per month"></canvas></div>
        </section>
    </div>

    <div class="fh-admin-subgrid">
        <section class="fh-card fh-card-pad" aria-labelledby="recentUsersTitle">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <h2 class="fh-card-title mb-0" id="recentUsersTitle">Latest members</h2>
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('admin.users.index') }}">View all</a>
            </div>

            @if($recentUsers->isEmpty())
                <x-empty title="No members yet" message="Registered members will appear here." icon="bi-people" />
            @else
                @php
                    $recentUserColumns = [
                        ['key' => 'name', 'label' => 'Member'],
                        ['key' => 'email', 'label' => 'Email'],
                        ['key' => 'role', 'label' => 'Role', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->role])->render()],
                        ['key' => 'created_at', 'label' => 'Joined', 'format' => fn ($row) => e($row->created_at?->format('d M Y'))],
                    ];
                @endphp

                <x-admin.table :items="$recentUsers" :columns="$recentUserColumns" />
            @endif
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="recentContentTitle">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <h2 class="fh-card-title mb-0" id="recentContentTitle">Latest content</h2>
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('admin.content.index') }}">View all</a>
            </div>

            @if($recentContent->isEmpty())
                <x-empty title="No content yet" message="Titles you add will show up here." icon="bi-film">
                    <a class="btn btn-fh" href="{{ route('admin.content.create') }}">Add content</a>
                </x-empty>
            @else
                @php
                    $recentContentColumns = [
                        ['key' => 'title', 'label' => 'Title'],
                        ['key' => 'category.name', 'label' => 'Category'],
                        ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                        ['key' => 'created_at', 'label' => 'Added', 'format' => fn ($row) => e($row->created_at?->format('d M Y'))],
                    ];
                @endphp

                <x-admin.table :items="$recentContent" :columns="$recentContentColumns" />
            @endif
        </section>

        <section class="fh-card fh-card-pad" aria-labelledby="recentFeedbackTitle">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <h2 class="fh-card-title mb-0" id="recentFeedbackTitle">Latest feedback
                    @if($openFeedback > 0)
                        <x-badge variant="red">{{ $openFeedback }} open</x-badge>
                    @endif
                </h2>
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('admin.feedback.index') }}">View all</a>
            </div>

            @if($recentFeedback->isEmpty())
                <x-empty title="No feedback yet" message="Member feedback will land here." icon="bi-chat-quote" />
            @else
                @php
                    $recentFeedbackColumns = [
                        ['key' => 'subject', 'label' => 'Subject'],
                        ['key' => 'type', 'label' => 'Type', 'format' => fn ($row) => '<span class="fh-chip">'.e(ucfirst($row->type)).'</span>'],
                        ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                        ['key' => 'created_at', 'label' => 'Received', 'format' => fn ($row) => e($row->created_at?->format('d M Y'))],
                    ];
                @endphp

                <x-admin.table :items="$recentFeedback" :columns="$recentFeedbackColumns" />
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
