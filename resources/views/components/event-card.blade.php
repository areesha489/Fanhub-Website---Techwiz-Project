@props(['item'])

@php
    $eventTitle = (string) data_get($item, 'name');
    $eventSlug = data_get($item, 'slug');
    $eventCategory = data_get($item, 'category');
    $eventStart = data_get($item, 'start_date');
    $eventBookmarked = (bool) data_get($item, 'bookmarked', false);
    $eventBookmarkId = data_get($item, 'bookmark_id');
    $eventUrl = route('events.show', $eventSlug);
    $eventCover = img(
        data_get($item, 'cover_image'),
        $eventTitle,
        800,
        450,
        data_get($eventCategory, 'slug')
    );

    $eventStartCarbon = $eventStart ? \Illuminate\Support\Carbon::parse($eventStart) : null;
    $eventStatus = data_get($item, 'status') ?: 'upcoming';

    $eventStatusTone = match ($eventStatus) {
        'ongoing' => 'green',
        'completed' => 'slate',
        'cancelled' => 'red',
        default => 'cyan',
    };
@endphp

<article {{ $attributes->merge(['class' => 'fh-card fh-card-hover']) }}>
    <div class="fh-card-media fh-media fh-media-16x9">
        <a href="{{ $eventUrl }}" aria-label="{{ 'Open event '.$eventTitle }}">
            <img
                class="fh-img"
                src="{{ $eventCover }}"
                alt="{{ $eventTitle }}"
                width="800"
                height="450"
                loading="lazy"
                decoding="async"
            >
        </a>

        @if($eventStartCarbon)
            <span class="fh-date-block" aria-hidden="true">
                <strong>{{ $eventStartCarbon->format('d') }}</strong>
                <span>{{ $eventStartCarbon->format('M') }}</span>
            </span>
        @endif

        <div class="fh-card-top-actions">
            <x-bookmark
                type="event"
                :id="data_get($item, 'id')"
                :bookmarked="$eventBookmarked"
                :bookmark-id="$eventBookmarkId"
                :note="data_get($item, 'bookmark_note')"
                size="sm"
            />
        </div>
    </div>

    <div class="fh-card-body">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if(data_get($item, 'event_type'))
                <span class="fh-chip fh-chip-magenta">
                    <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                    {{ data_get($item, 'event_type') }}
                </span>
            @endif

            <x-badge variant="{{ $eventStatusTone }}">{{ $eventStatus }}</x-badge>
        </div>

        <h3 class="fh-card-title fh-clamp-2">
            <a href="{{ $eventUrl }}">{{ $eventTitle }}</a>
        </h3>

        <div class="fh-card-meta">
            @if(data_get($item, 'city') || data_get($item, 'venue'))
                <span class="fh-meta-item fh-clamp-2">
                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                    {{ collect([data_get($item, 'venue'), data_get($item, 'city')])->filter()->implode(', ') }}
                </span>
            @endif

            @if($eventStartCarbon)
                <span class="fh-meta-item">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    {{ $eventStartCarbon->format('M j, Y') }}
                    @if(data_get($item, 'end_date'))
                        – {{ \Illuminate\Support\Carbon::parse(data_get($item, 'end_date'))->format('M j, Y') }}
                    @endif
                </span>
            @endif
        </div>

        <div class="fh-card-foot">
            <span class="fh-meta-item fh-text-dim fh-text-sm">
                <i class="bi bi-eye" aria-hidden="true"></i>
                {{ number_format((int) data_get($item, 'views_count', 0)) }}
            </span>
            <a class="fh-card-cta" href="{{ $eventUrl }}">
                View details <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</article>
