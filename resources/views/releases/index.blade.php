@extends('layouts.app')

@section('title', 'Upcoming Releases · Fan Hub+')

@section('content')
<x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Releases']]" />

<x-page-banner
    kicker="Dates to circle"
    title="Upcoming Releases"
    subtitle="Anime seasons, game launches, films, comics and collectible drops — every date the fandom is waiting for."
    image="{{ asset('images/catalogue/banner-releases.png') }}"
/>

@if($error !== null)
    <x-error :message="$error">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('releases.index') }}">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Try again
        </a>
    </x-error>
@else
    @php
        $hasFilters = $type !== '' || $status !== '' || $sort !== 'date';
        $typeLabels = [
            'anime' => 'Anime',
            'game' => 'Game',
            'movie' => 'Movie',
            'tv' => 'TV',
            'comic' => 'Comic',
            'merchandise' => 'Merchandise',
        ];
    @endphp

    <form class="fh-toolbar" action="{{ route('releases.index') }}" method="get">
        <div class="fh-filter-group mb-0">
            <label class="fh-label" for="release-type">Type</label>
            <select class="fh-field" id="release-type" name="type" onchange="this.form.submit()" style="width:auto">
                <option value="">All types</option>
                @foreach($types as $typeOption)
                    <option value="{{ $typeOption }}" @selected($type === $typeOption)>{{ $typeLabels[$typeOption] ?? ucfirst($typeOption) }}</option>
                @endforeach
            </select>
        </div>

        <div class="fh-filter-group mb-0">
            <label class="fh-label" for="release-status">Status</label>
            <select class="fh-field" id="release-status" name="status" onchange="this.form.submit()" style="width:auto">
                <option value="">Any status</option>
                <option value="upcoming" @selected($status === 'upcoming')>Upcoming</option>
                <option value="released" @selected($status === 'released')>Released</option>
                <option value="delayed" @selected($status === 'delayed')>Delayed</option>
            </select>
        </div>

        <div class="fh-filter-group mb-0">
            <label class="fh-label" for="release-sort">Sort by</label>
            <select class="fh-field" id="release-sort" name="sort" onchange="this.form.submit()" style="width:auto">
                <option value="date" @selected($sort === 'date')>Release date</option>
                <option value="popular" @selected($sort === 'popular')>Most anticipated</option>
                <option value="alphabetical" @selected($sort === 'alphabetical')>A → Z</option>
            </select>
        </div>

        <button class="btn btn-fh-outline btn-sm align-self-end" type="submit">
            <i class="bi bi-funnel" aria-hidden="true"></i> Apply
        </button>
    </form>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <p class="fh-eyebrow mb-0">{{ number_format($total) }} {{ $total === 1 ? 'release' : 'releases' }} listed</p>

        @if($hasFilters)
            <a class="fh-chip fh-chip-slate" href="{{ route('releases.index') }}">
                <i class="bi bi-x-circle" aria-hidden="true"></i> Clear filters
            </a>
        @endif
    </div>

    <div class="fh-layout-with-aside fh-aside-right">
        <div class="min-w-0">
            @if($releases->isEmpty())
                @if($hasFilters)
                    <x-empty
                        title="No releases match those filters"
                        message="Nothing on the slate fits that combination yet — switch type or status to see the rest."
                        icon="bi-calendar-x"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('releases.index') }}">
                            <i class="bi bi-x-circle" aria-hidden="true"></i> Clear filters
                        </a>
                    </x-empty>
                @else
                    <x-empty
                        title="No releases scheduled yet"
                        message="Launch dates will appear here as soon as publishers and studios confirm them."
                        icon="bi-clock-history"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('events.index') }}">
                            <i class="bi bi-calendar-event" aria-hidden="true"></i> Browse events
                        </a>
                    </x-empty>
                @endif
            @else
                <div class="fh-grid fh-grid-3">
                    @foreach($releases as $release)
                        @php
                            $daysUntil = null;

                            if ($release->release_date) {
                                $daysUntil = (int) ceil(
                                    (strtotime($release->release_date->toDateString()) - strtotime(now()->toDateString())) / 86400
                                );
                            }
                        @endphp

                        <div class="h-100 d-flex flex-column gap-2">
                            @if($release->status === 'upcoming' && $daysUntil !== null)
                                <span class="fh-badge {{ $daysUntil < 0 ? 'fh-badge-amber' : 'fh-badge-violet' }} rounded-pill align-self-start">
                                    <i class="bi bi-hourglass-split" aria-hidden="true"></i>
                                    {{ $daysUntil > 0 ? $daysUntil.' days to go' : ($daysUntil === 0 ? 'Releases today' : 'Date awaiting update') }}
                                </span>
                            @endif

                            <x-release-card :item="$release" />
                        </div>
                    @endforeach
                </div>

                <x-pagination :paginator="$releases" />
            @endif
        </div>

        <aside class="d-flex flex-column gap-3" aria-label="Release sidebar">
            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-calendar2-day" aria-hidden="true"></i> This Month
                </p>

                @if($thisMonth->isEmpty())
                    <p class="fh-text-dim fh-text-sm mb-0">Nothing lands this month — enjoy the backlog.</p>
                @else
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                        @foreach($thisMonth as $monthRelease)
                            <li class="d-flex align-items-center gap-2">
                                <span class="fh-date-block" style="position: relative; left: auto; bottom: auto; flex: 0 0 auto;" aria-hidden="true">
                                    <strong>{{ $monthRelease->release_date->format('d') }}</strong>
                                    <span>{{ $monthRelease->release_date->format('M') }}</span>
                                </span>
                                <span class="min-w-0">
                                    <span class="fh-fw-600 fh-clamp-2 d-block">{{ $monthRelease->title }}</span>
                                    <span class="fh-text-dim fh-text-sm">{{ $typeLabels[$monthRelease->release_type] ?? ucfirst($monthRelease->release_type) }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-funnel" aria-hidden="true"></i> Quick statuses
                </p>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li>
                        <a class="fh-card-cta" href="{{ route('releases.index', ['status' => 'upcoming']) }}">
                            <i class="bi bi-hourglass-split" aria-hidden="true"></i> Still upcoming
                        </a>
                    </li>
                    <li>
                        <a class="fh-card-cta" href="{{ route('releases.index', ['status' => 'released']) }}">
                            <i class="bi bi-check2-circle" aria-hidden="true"></i> Already released
                        </a>
                    </li>
                    <li>
                        <a class="fh-card-cta" href="{{ route('releases.index', ['status' => 'delayed']) }}">
                            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Delayed
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
@endif
@endsection
