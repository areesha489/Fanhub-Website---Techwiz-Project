@props(['item'])

@php
    $name = (string) data_get($item, 'name');
    $slug = data_get($item, 'slug');
    $category = data_get($item, 'category');
    $start = data_get($item, 'start_date') ? \Illuminate\Support\Carbon::parse(data_get($item, 'start_date')) : null;
    $place = collect([data_get($item, 'venue'), data_get($item, 'city')])->filter()->implode(', ');
@endphp

<a class="fh-cal-card" href="{{ route('events.show', $slug) }}">
    @if($start)
        <span class="fh-cal-date">
            <strong>{{ $start->format('d') }}</strong>
            <small>{{ strtoupper($start->format('M')) }}</small>
        </span>
    @endif
    <span class="fh-cal-copy">
        <strong>{{ $name }}</strong>
        @if($place !== '')
            <span>{{ $place }}</span>
        @endif
        <span class="fh-rail-tags">
            @if($category)
                <em>{{ data_get($category, 'name') }}</em>
            @endif
            @if(data_get($item, 'event_type'))
                <em>{{ data_get($item, 'event_type') }}</em>
            @endif
        </span>
    </span>
</a>
