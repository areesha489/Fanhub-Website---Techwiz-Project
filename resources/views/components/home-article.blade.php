@props(['item'])

@php
    $title = (string) data_get($item, 'title');
    $slug = data_get($item, 'slug');
    $category = data_get($item, 'category');
    $published = data_get($item, 'published_at') ?: data_get($item, 'created_at');
    $url = route('articles.show', $slug);
    $storedCover = data_get($item, 'cover_image');
    $cover = is_string($storedCover) && $storedCover !== ''
        ? $storedCover
        : catalogueStill(data_get($category, 'slug'), $title);
    $when = $published ? \Illuminate\Support\Carbon::parse($published)->diffForHumans() : '';
@endphp

<article {{ $attributes->merge(['class' => 'fh-story']) }}>
    <a class="fh-story-media" href="{{ $url }}">
        <img src="{{ $cover }}" alt="" width="640" height="360" loading="lazy" decoding="async">
    </a>
    <div class="fh-story-copy">
        @if($category)
            <a class="fh-mini-chip" href="{{ route('category.show', data_get($category, 'slug')) }}">{{ data_get($category, 'name') }}</a>
        @endif
        <h3><a href="{{ $url }}">{{ $title }}</a></h3>
        @if($when !== '')
            <p>{{ $when }}</p>
        @endif
    </div>
</article>
