@props(['item'])

@php
    $title = (string) data_get($item, 'title');
    $slug = data_get($item, 'slug');
    $category = data_get($item, 'category');
    $author = data_get($item, 'user.name');
    $url = filled($slug) ? route('community.show', $slug) : route('community.index');
    $cover = $item->displayImage();
@endphp

<article {{ $attributes->merge(['class' => 'fh-story']) }}>
    <a class="fh-story-media" href="{{ $url }}">
        <img src="{{ $cover }}" alt="" width="640" height="360" loading="lazy" decoding="async">
    </a>
    <div class="fh-story-copy">
        @if($category)
            <a class="fh-mini-chip" href="{{ route('category.show', data_get($category, 'slug')) }}">{{ data_get($category, 'name') }}</a>
        @else
            <span class="fh-mini-chip">{{ \Illuminate\Support\Str::headline((string) data_get($item, 'type', 'post')) }}</span>
        @endif
        <h3><a href="{{ $url }}">{{ $title }}</a></h3>
        @if($author)
            <p>{{ $author }}</p>
        @endif
    </div>
</article>
