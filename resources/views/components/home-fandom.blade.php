@props(['item'])

@php
    $slug = (string) data_get($item, 'slug');
    $name = (string) data_get($item, 'name');
    $count = (int) data_get($item, 'content_count', 0);
    $blurb = \Illuminate\Support\Str::limit((string) data_get($item, 'description'), 72);
    $cover = img(data_get($item, 'image'), $name, 900, 560, $slug);
    $countLabel = $count === 1 ? '1 title' : $count.' titles';
@endphp

<a class="fh-fandom-card" href="{{ route('category.show', $slug) }}">
    <img src="{{ $cover }}" alt="" width="900" height="560" loading="lazy" decoding="async">
    <span class="fh-fandom-copy">
        <strong>{{ $name }}</strong>
        @if($blurb !== '')
            <span>{{ $blurb }}</span>
        @endif
        <em>{{ $countLabel }}</em>
    </span>
</a>
