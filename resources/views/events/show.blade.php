@extends('layouts.app')

@section('title', $event?->name ? $event->name.' · Events · Fan Hub+' : 'Events · Fan Hub+')

@section('styles')
<style>
    .fh-event-hero {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        gap: .35rem;
        padding: clamp(1rem, 3vw, 2.25rem);
    }

    .fh-map-box {
        background-color: #060b18;
        background-image:
            linear-gradient(rgba(255, 255, 255, .05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, .05) 1px, transparent 1px);
        background-size: 28px 28px;
        border: 1px solid var(--fh-border);
        border-radius: var(--fh-radius-lg);
        padding: clamp(1.1rem, 3vw, 1.75rem);
    }

    .fh-detail-list {
        display: flex;
        flex-direction: column;
        gap: .85rem;
        margin: 0;
    }

    .fh-detail-list > div {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .fh-detail-list dt {
        color: var(--fh-dim);
        font-size: .88rem;
        font-weight: 500;
    }

    .fh-detail-list dd {
        margin: 0;
        text-align: right;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
@if($loadError !== null || $event === null)
    <x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Events', 'to' => route('events.index')]]" />
    <x-error :message="$loadError ?? 'We could not load this event right now. Please try again in a moment. The link you followed may point to an event that was removed.'">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('events.index') }}">
            <i class="bi bi-calendar-event" aria-hidden="true"></i> Back to events
        </a>
    </x-error>
@else
    @php
        $eventCategory = $event->category;

        $statusTone = match ($event->status) {
            'ongoing' => 'green',
            'completed' => 'slate',
            'cancelled' => 'red',
            default => 'cyan',
        };

        $start = $event->start_date;
        $end = $event->end_date;

        $dateRange = 'Dates to be confirmed';
        if ($start && $end && $start->isSameDay($end)) {
            $dateRange = $start->format('D, M j, Y');
        } elseif ($start && $end) {
            $dateRange = $start->format('M j, Y').' – '.$end->format('M j, Y');
        } elseif ($start) {
            $dateRange = $start->format('M j, Y');
        }

        $timeLabel = null;
        if (filled($event->time)) {
            try {
                $timeLabel = \Illuminate\Support\Carbon::parse($event->time)->format('g:i A');
            } catch (\Throwable $exception) {
                $timeLabel = (string) $event->time;
            }
        }

        $placeLine = collect([$event->venue, $event->city, $event->country])->filter()->implode(', ');

        $hasCoordinates = filled($event->latitude) && filled($event->longitude);
        $mapsUrl = $hasCoordinates
            ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($event->latitude.','.$event->longitude)
            : null;
    @endphp

    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Events', 'to' => route('events.index')],
        ['label' => $event->name],
    ]" />

    <div class="fh-card mb-4" style="overflow: hidden;">
        <div class="fh-media fh-media-16x9" style="min-height: 260px;">
            <img
                class="fh-img"
                src="{{ img($event->cover_image, $event->name, 1600, 900, $eventCategory?->slug) }}"
                alt="Cover artwork for {{ $event->name }}"
                width="1600"
                height="900"
                decoding="async"
            >
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(5, 8, 22, .18) 30%, rgba(5, 8, 22, .94) 100%);">
                <div class="fh-event-hero">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @if($event->event_type)
                            <x-badge variant="pink">
                                <i class="bi bi-ticket-perforated" aria-hidden="true"></i> {{ $event->event_type }}
                            </x-badge>
                        @endif
                        <x-badge variant="{{ $statusTone }}">{{ $event->status }}</x-badge>
                        @if($eventCategory)
                            <a class="fh-chip" href="{{ route('category.show', $eventCategory->slug) }}">
                                <i class="bi {{ $eventCategory->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                                {{ $eventCategory->name }}
                            </a>
                        @endif
                    </div>

                    <h1 class="fh-title mb-0" style="font-size: clamp(1.6rem, 4vw, 2.75rem);">{{ $event->name }}</h1>

                    <p class="fh-text-muted-2 mb-0">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        {{ $placeLine ?: 'Location to be announced' }}
                        <span class="fh-text-dim">·</span>
                        {{ $dateRange }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="fh-layout-with-aside fh-aside-right">
        <div class="min-w-0">
            <section class="fh-card fh-card-pad mb-4">
                <div class="fh-section-head mb-3">
                    <div class="fh-section-head-main">
                        <span class="fh-section-bar" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <h2 class="fh-section-title">Event details</h2>
                            <p class="fh-section-sub">When and where it all happens</p>
                        </div>
                    </div>
                </div>

                <dl class="fh-detail-list mb-0">
                    <div>
                        <dt>Date range</dt>
                        <dd>{{ $dateRange }}</dd>
                    </div>
                    <div>
                        <dt>Start time</dt>
                        <dd>{{ $timeLabel ?: 'To be announced' }}</dd>
                    </div>
                    <div>
                        <dt>Venue</dt>
                        <dd>{{ $event->venue ?: 'To be announced' }}</dd>
                    </div>
                    <div>
                        <dt>City</dt>
                        <dd>{{ collect([$event->city, $event->country])->filter()->implode(', ') ?: 'To be announced' }}</dd>
                    </div>
                    <div>
                        <dt>Format</dt>
                        <dd>{{ $event->event_type ?: 'General' }}</dd>
                    </div>
                    <div>
                        <dt>Status</dt>
                        <dd><x-badge variant="{{ $statusTone }}">{{ $event->status }}</x-badge></dd>
                    </div>
                </dl>
            </section>

            <section class="fh-map-box mb-4" aria-label="Event location">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="min-w-0">
                        <p class="fh-eyebrow mb-2">
                            <i class="bi bi-pin-map" aria-hidden="true"></i> Map &amp; location
                        </p>
                        <p class="fh-text-muted-2 mb-1">{{ $placeLine ?: 'The venue has not been announced yet.' }}</p>

                        @if($hasCoordinates)
                            <p class="fh-text-dim fh-text-sm fh-mono mb-0">
                                {{ $event->latitude }}°, {{ $event->longitude }}°
                            </p>
                        @else
                            <p class="fh-text-dim fh-text-sm mb-0">Exact coordinates will be published once the venue is confirmed.</p>
                        @endif
                    </div>

                    @if($mapsUrl)
                        <a class="btn btn-fh-outline btn-sm" href="{{ $mapsUrl }}" target="_blank" rel="noopener">
                            <i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Open in Google Maps
                        </a>
                    @endif
                </div>
            </section>

            <section class="fh-card fh-card-pad mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <p class="fh-filter-title mb-0">
                        <i class="bi bi-ticket-perforated" aria-hidden="true"></i> Tickets
                    </p>

                    @if($event->ticket_url)
                        <a class="btn btn-fh" href="{{ $event->ticket_url }}" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Get tickets
                        </a>
                    @endif
                </div>

                @if(! $event->ticket_url)
                    <x-empty
                        title="Tickets not released yet"
                        message="Ticketing opens closer to the date — the link will appear here as soon as sales go live."
                        icon="bi-ticket-detailed"
                    />
                @else
                    <p class="fh-text-dim fh-text-sm mb-0">
                        Ticket sales are handled by the organiser. Fan Hub+ links out only — we never process payments.
                    </p>
                @endif
            </section>

            @if($event->description)
                <section class="fh-section mb-4">
                    <div class="fh-section-head">
                        <div class="fh-section-head-main">
                            <span class="fh-section-bar" aria-hidden="true"></span>
                            <div class="min-w-0">
                                <h2 class="fh-section-title">About this event</h2>
                                <p class="fh-section-sub">What to expect on the day</p>
                            </div>
                        </div>
                    </div>

                    <div class="fh-card fh-card-pad" style="max-width: 78ch;">
                        @foreach(collect(preg_split('/\R+/', (string) $event->description))->map(fn (string $paragraph): string => trim($paragraph))->filter() as $paragraph)
                            <p class="fh-text-muted-2">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($related->isNotEmpty())
                <x-section
                    title="Related Events"
                    subtitle="More gatherings in the same corner of the fandom"
                    :href="$eventCategory ? route('events.index', ['category' => $eventCategory->slug]) : route('events.index')"
                    linkText="View calendar"
                >
                    <div class="fh-grid fh-grid-wide">
                        @foreach($related as $relatedEvent)
                            <x-event-card :item="$relatedEvent" />
                        @endforeach
                    </div>
                </x-section>
            @endif
        </div>

        <aside class="d-flex flex-column gap-3" aria-label="Event sidebar">
            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-calendar2-week" aria-hidden="true"></i> At a glance
                </p>

                <div class="d-flex align-items-center gap-3 mb-3">
                    @if($start)
                        <span class="fh-date-block" style="position: relative; left: auto; bottom: auto; flex: 0 0 auto;" aria-hidden="true">
                            <strong>{{ $start->format('d') }}</strong>
                            <span>{{ $start->format('M') }}</span>
                        </span>
                    @endif
                    <div class="min-w-0">
                        <div class="fh-fw-600">{{ $dateRange }}</div>
                        <div class="fh-text-dim fh-text-sm">{{ $timeLabel ? 'Doors '.$timeLabel : 'Time to be announced' }}</div>
                    </div>
                </div>

                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 fh-text-sm">
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Type</span>
                        <span class="fh-fw-600">{{ $event->event_type ?: 'General' }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Status</span>
                        <span><x-badge variant="{{ $statusTone }}">{{ $event->status }}</x-badge></span>
                    </li>
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Views</span>
                        <span class="fh-fw-600">{{ number_format((int) $event->views_count) }}</span>
                    </li>
                </ul>
            </div>

            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-bookmark-star" aria-hidden="true"></i> Save &amp; share
                </p>

                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <x-bookmark
                        type="event"
                        :id="$event->id"
                        :bookmarked="$isBookmarked"
                        :bookmark-id="$bookmarkId"
                    />
                    <button
                        type="button"
                        class="btn btn-fh-outline btn-sm"
                        data-share-copy
                        aria-label="Share this event"
                    >
                        <i class="bi bi-share" aria-hidden="true"></i> Share
                    </button>
                </div>

                <p class="fh-text-dim fh-text-sm mb-0">Bookmarks sync to your dashboard so you never miss a date.</p>
            </div>

            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-compass" aria-hidden="true"></i> Keep exploring
                </p>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li>
                        <a class="fh-card-cta" href="{{ route('events.index') }}">
                            <i class="bi bi-calendar3" aria-hidden="true"></i> Full calendar
                        </a>
                    </li>
                    <li>
                        <a class="fh-card-cta" href="{{ route('releases.index') }}">
                            <i class="bi bi-clock-history" aria-hidden="true"></i> Upcoming releases
                        </a>
                    </li>
                    <li>
                        <a class="fh-card-cta" href="{{ route('media.index') }}">
                            <i class="bi bi-play-circle" aria-hidden="true"></i> Multimedia centre
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
@endif
@endsection

@section('scripts')
<script>
    document.querySelectorAll('[data-share-copy]').forEach(function (button) {
        button.addEventListener('click', async function () {
            var url = window.location.href;
            try {
                if (navigator.share) {
                    await navigator.share({ title: document.title, url: url });
                    return;
                }
                await navigator.clipboard.writeText(url);
                if (window.FanHub && FanHub.toast) FanHub.toast('success', 'Link copied to your clipboard.');
            } catch (error) {
                if (window.FanHub && FanHub.toast) FanHub.toast('info', 'Copy this link to share: ' + url);
            }
        });
    });
</script>
@endsection
