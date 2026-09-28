@props(['item'])

@php
    $title = (string) data_get($item, 'title');
    $type = (string) (data_get($item, 'release_type') ?: 'release');
    $date = data_get($item, 'release_date');
    $content = data_get($item, 'content');
    $url = ($content && data_get($content, 'slug'))
        ? route('content.show', data_get($content, 'slug'))
        : route('releases.index');
    $poster = img(data_get($item, 'poster'), $title, 120, 120, data_get($item, 'category.slug'));
    $when = $date ? \Illuminate\Support\Carbon::parse($date)->format('M Y') : 'Soon';
    $typeLabel = match ($type) {
        'tv' => 'TV Show',
        'game' => 'Game',
        'movie' => 'Movie',
        'comic' => 'Comic',
        'merchandise' => 'Merch',
        'anime' => 'Anime',
        default => ucfirst($type),
    };
@endphp

<a class="fh-rail-release" href="{{ $url }}">
    <img src="{{ $poster }}" alt="" width="44" height="44" loading="lazy" decoding="async">
    <span>
        <strong>{{ $title }}</strong>
        <small>{{ $typeLabel }} · {{ $when }}</small>
    </span>
    <i class="bi bi-chevron-right" aria-hidden="true"></i>
</a>
