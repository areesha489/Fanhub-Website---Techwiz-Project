@props(['item'])

@php
    $name = (string) data_get($item, 'name');
    $slug = data_get($item, 'slug');
    $category = data_get($item, 'category');
    $fandom = (string) (data_get($item, 'fandom') ?: data_get($category, 'name'));
    $url = route('characters.show', $slug);
    $image = img(data_get($item, 'image'), $name, 480, 640, data_get($category, 'slug'));
@endphp

<article {{ $attributes->merge(['class' => 'fh-face']) }}>
    <a class="fh-face-media" href="{{ $url }}">
        <img src="{{ $image }}" alt="{{ $name }}" width="480" height="640" loading="lazy" decoding="async">
    </a>
    <div class="fh-poster-save">
        <x-bookmark
            type="character"
            :id="data_get($item, 'id')"
            :bookmarked="(bool) data_get($item, 'bookmarked', false)"
            :bookmark-id="data_get($item, 'bookmark_id')"
            :note="data_get($item, 'bookmark_note')"
            size="sm"
        />
    </div>
    <div class="fh-poster-copy">
        <h3><a href="{{ $url }}">{{ $name }}</a></h3>
        @if($fandom !== '')
            <p>{{ $fandom }}</p>
        @endif
        <a class="fh-view-profile" href="{{ $url }}">View Profile</a>
    </div>
</article>
