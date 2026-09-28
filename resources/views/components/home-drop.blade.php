@props(['item'])

@php
    $title = (string) data_get($item, 'title');
    $type = (string) (data_get($item, 'release_type') ?: 'release');
    $date = data_get($item, 'release_date');
    $content = data_get($item, 'content');
    $url = ($content && data_get($content, 'slug'))
        ? route('content.show', data_get($content, 'slug'))
        : route('releases.index');
    $poster = img(data_get($item, 'poster'), $title, 480, 640, data_get($item, 'category.slug'));
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

<a class="fh-drop" href="{{ $url }}">
    <img src="{{ $poster }}" alt="" width="480" height="640" loading="lazy" decoding="async">
    <span>
        <strong>{{ $title }}</strong>
        <small>{{ $typeLabel }} · {{ $when }}</small>
    </span>
</a>
