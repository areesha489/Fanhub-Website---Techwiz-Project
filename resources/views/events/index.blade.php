@extends('layouts.app')

@section('title', 'Events · Fan Hub+')

@section('content')
<x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Events']]" />

<x-page-banner
    kicker="Mark your calendar"
    title="Upcoming Events"
    subtitle="Conventions, meetups, tournaments and more — find the gatherings worth travelling for."
    image="{{ asset('images/catalogue/banner-events.png') }}"
/>

@if($error !== null)
    <x-error :message="$error">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('events.index') }}">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Try again
        </a>
    </x-error>
@else
    @php
        $hasFilters = $city !== '' || $category !== '' || $type !== '' || $status !== '';
        $statusLabels = [
            'upcoming' => 'Upcoming',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    @endphp

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <p class="fh-eyebrow mb-0">
            {{ number_format($total) }} {{ $total === 1 ? 'event' : 'events' }} listed
            @if($totalUpcoming > 0)
                · {{ number_format($totalUpcoming) }} still upcoming
            @endif
        </p>

        @if($hasFilters)
            <a class="fh-chip fh-chip-slate" href="{{ route('events.index', ['sort' => $sort]) }}">
                <i class="bi bi-x-circle" aria-hidden="true"></i> Clear filters
            </a>
        @endif
    </div>

    <div class="fh-layout-with-aside">
        <x-filter-panel :action="route('events.index')" title="Filter events">
            <div class="fh-filter-group">
                <label class="fh-label" for="events-city">City</label>
                <select class="fh-field" id="events-city" name="city" onchange="this.form.submit()">
                    <option value="">Every city</option>
                    @foreach($cities as $cityOption)
                        <option value="{{ $cityOption }}" @selected($city === $cityOption)>{{ $cityOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="events-category">Category</label>
                <select class="fh-field" id="events-category" name="category" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach($categories as $categoryOption)
                        <option value="{{ $categoryOption->slug }}" @selected($category === $categoryOption->slug)>{{ $categoryOption->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="events-type">Event type</label>
                <select class="fh-field" id="events-type" name="type" onchange="this.form.submit()">
                    <option value="">All types</option>
                    @foreach($eventTypes as $typeOption)
                        <option value="{{ $typeOption }}" @selected($type === $typeOption)>{{ $typeOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="events-status">Status</label>
                <select class="fh-field" id="events-status" name="status" onchange="this.form.submit()">
                    <option value="">Any status</option>
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption }}" @selected($status === $statusOption)>
                            {{ $statusLabels[$statusOption] ?? ucfirst($statusOption) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="events-sort">Sort by</label>
                <select class="fh-field" id="events-sort" name="sort" onchange="this.form.submit()">
                    <option value="date" @selected($sort === 'date')>Date (soonest first)</option>
                    <option value="popular" @selected($sort === 'popular')>Most viewed</option>
                    <option value="latest" @selected($sort === 'latest')>Recently added</option>
                </select>
            </div>

            <button class="btn btn-fh w-100" type="submit">
                <i class="bi bi-funnel" aria-hidden="true"></i> Apply filters
            </button>
        </x-filter-panel>

        <div class="min-w-0">
            @if($events->isEmpty())
                @if($hasFilters)
                    <x-empty
                        title="No events match those filters"
                        message="Nothing on the calendar fits that combination yet — widen the search to see every gathering."
                        icon="bi-calendar-x"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('events.index') }}">
                            <i class="bi bi-x-circle" aria-hidden="true"></i> Clear filters
                        </a>
                    </x-empty>
                @else
                    <x-empty
                        title="No events on the calendar yet"
                        message="Conventions, meetups and tournaments will be listed here as soon as they are announced."
                        icon="bi-calendar-event"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('releases.index') }}">
                            <i class="bi bi-clock-history" aria-hidden="true"></i> See upcoming releases
                        </a>
                    </x-empty>
                @endif
            @else
                <div class="fh-grid fh-grid-wide">
                    @foreach($events as $event)
                        <x-event-card :item="$event" />
                    @endforeach
                </div>

                <x-pagination :paginator="$events" />
            @endif
        </div>
    </div>
@endif
@endsection
