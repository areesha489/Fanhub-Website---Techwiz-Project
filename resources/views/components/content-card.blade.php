@props(['item'])

@php
    $contentTitle = (string) data_get($item, 'title');
    $contentSlug = data_get($item, 'slug');
    $contentCategory = data_get($item, 'category');
    $contentViews = (int) data_get($item, 'views_count', 0);
    $contentRating = (float) data_get($item, 'rating', 0);
    $contentDate = data_get($item, 'release_date');
    $contentBookmarked = (bool) data_get($item, 'bookmarked', false);
    $contentBookmarkId = data_get($item, 'bookmark_id');
    $contentUrl = route('content.show', $contentSlug);
    $contentCover = img(
        data_get($item, 'cover_image'),
        $contentTitle,
        800,
        450,
        data_get($item, 'category.slug')
    );
@endphp

<article {{ $attributes->merge(['class' => 'fh-card fh-card-hover']) }}>
    <div class="fh-card-media fh-media fh-media-16x9">
        <a href="{{ $contentUrl }}" aria-label="{{ 'Open '.$contentTitle }}">
            <img
                class="fh-img"
                src="{{ $contentCover }}"
                alt="{{ $contentTitle }}"
                width="800"
                height="450"
                loading="lazy"
                decoding="async"
            >
        </a>

        <div class="fh-card-top-actions">
            <x-bookmark
                type="content"
                :id="data_get($item, 'id')"
                :bookmarked="$contentBookmarked"
                :bookmark-id="$contentBookmarkId"
                :note="data_get($item, 'bookmark_note')"
                size="sm"
            />
        </div>

        @if($contentRating > 0)
            <span class="fh-poster-rating">
                <i class="bi bi-star-fill" aria-hidden="true"></i>
                {{ number_format($contentRating, 1) }}
            </span>
        @endif
    </div>

    <div class="fh-card-body">
        @if($contentCategory)
            <a class="fh-chip" href="{{ route('category.show', data_get($contentCategory, 'slug')) }}">
                <i class="bi {{ data_get($contentCategory, 'icon') ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                {{ data_get($contentCategory, 'name') }}
            </a>
        @elseif(data_get($item, 'content_type'))
            <span class="fh-chip">{{ data_get($item, 'content_type') }}</span>
        @endif

        <h3 class="fh-card-title fh-clamp-2">
            <a href="{{ $contentUrl }}">{{ $contentTitle }}</a>
        </h3>

        <div class="fh-card-meta">
            <span class="fh-meta-item">
                <i class="bi bi-eye" aria-hidden="true"></i>
                {{ number_format($contentViews) }}
            </span>

            @if($contentRating > 0)
                <x-rating :value="$contentRating" size="sm" />
            @endif

            @if($contentDate)
                <span class="fh-meta-item">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    {{ \Illuminate\Support\Carbon::parse($contentDate)->format('M Y') }}
                </span>
            @endif
        </div>

        <div class="fh-card-foot">
            <span class="fh-text-dim fh-text-sm">
                {{ data_get($item, 'genre') ?: data_get($item, 'content_type') ?: 'Explore' }}
            </span>
            <a class="fh-card-cta" href="{{ $contentUrl }}">
                Details <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</article>
