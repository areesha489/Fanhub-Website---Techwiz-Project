@props(['item'])

@php
    $title = (string) data_get($item, 'title');
    $slug = data_get($item, 'slug');
    $category = data_get($item, 'category');
    $views = (int) data_get($item, 'views_count', 0);
    $rating = (float) data_get($item, 'rating', 0);
    $url = route('content.show', $slug);
    $cover = img(data_get($item, 'cover_image'), $title, 640, 860, data_get($category, 'slug'));
    $blurb = \Illuminate\Support\Str::limit((string) (data_get($item, 'description') ?: data_get($item, 'synopsis')), 42);
    $viewsLabel = $views >= 1000000
        ? rtrim(rtrim(number_format($views / 1000000, 1), '0'), '.').'M'
        : ($views >= 1000 ? (string) (int) round($views / 1000).'K' : (string) $views);
@endphp

<article {{ $attributes->merge(['class' => 'fh-poster']) }}>
    <a class="fh-poster-media" href="{{ $url }}">
        <img src="{{ $cover }}" alt="{{ $title }}" width="640" height="860" loading="lazy" decoding="async">
    </a>
    <div class="fh-poster-save">
        <x-bookmark
            type="content"
            :id="data_get($item, 'id')"
            :bookmarked="(bool) data_get($item, 'bookmarked', false)"
            :bookmark-id="data_get($item, 'bookmark_id')"
            :note="data_get($item, 'bookmark_note')"
            size="sm"
        />
    </div>
    <div class="fh-poster-copy">
        @if($category)
            <a class="fh-mini-chip" href="{{ route('category.show', data_get($category, 'slug')) }}">{{ data_get($category, 'name') }}</a>
        @endif
        <h3><a href="{{ $url }}">{{ $title }}</a></h3>
        @if($blurb !== '')
            <p>{{ $blurb }}</p>
        @endif
        <div class="fh-poster-meta">
            <span><i class="bi bi-eye" aria-hidden="true"></i> {{ $viewsLabel }}</span>
            @if($rating > 0)
                <span><i class="bi bi-star-fill" aria-hidden="true"></i> {{ number_format($rating > 5 ? $rating / 2 : $rating, 1) }}</span>
            @endif
            <a class="fh-round-go" href="{{ $url }}" aria-label="Open {{ $title }}">
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</article>
