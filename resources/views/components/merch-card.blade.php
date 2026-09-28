@props(['item'])

@php
    $merchTitle = (string) (data_get($item, 'title') ?: data_get($item, 'name'));
    $merchSlug = data_get($item, 'slug');
    $merchCategory = data_get($item, 'category');
    $merchFandom = data_get($item, 'fandom');
    $merchPrice = data_get($item, 'price');
    $merchCurrency = data_get($item, 'currency') ?: 'USD';
    $merchTags = collect(data_get($item, 'tags') ?: []);
    $merchBookmarked = (bool) data_get($item, 'bookmarked', false);
    $merchBookmarkId = data_get($item, 'bookmark_id');
    $merchUpcoming = (bool) data_get($item, 'is_upcoming', false);
    $merchUrl = route('merchandise.show', $merchSlug);
    $merchImage = img(
        data_get($item, 'image'),
        $merchTitle,
        800,
        800,
        data_get($merchCategory, 'slug')
    );
@endphp

<article {{ $attributes->merge(['class' => 'fh-card fh-card-hover']) }}>
    <div class="fh-card-media fh-media fh-media-1x1">
        <a href="{{ $merchUrl }}" aria-label="{{ 'Open '.$merchTitle }}">
            <img
                class="fh-img"
                src="{{ $merchImage }}"
                alt="{{ $merchTitle }}"
                width="800"
                height="800"
                loading="lazy"
                decoding="async"
            >
        </a>

        <div class="fh-card-top-actions">
            <x-bookmark
                type="merchandise"
                :id="data_get($item, 'id')"
                :bookmarked="$merchBookmarked"
                :bookmark-id="$merchBookmarkId"
                :note="data_get($item, 'bookmark_note')"
                size="sm"
            />
        </div>

        @if($merchUpcoming)
            <span class="fh-poster-rating">
                <i class="bi bi-clock-history" aria-hidden="true"></i> Pre-order
            </span>
        @endif
    </div>

    <div class="fh-card-body">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($merchCategory)
                <a class="fh-chip fh-chip-amber" href="{{ route('category.show', data_get($merchCategory, 'slug')) }}">
                    <i class="bi {{ data_get($merchCategory, 'icon') ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                    {{ data_get($merchCategory, 'name') }}
                </a>
            @endif
        </div>

        <h3 class="fh-card-title fh-clamp-2">
            <a href="{{ $merchUrl }}">{{ $merchTitle }}</a>
        </h3>

        @if($merchFandom)
            <p class="fh-text-muted-2 fh-text-sm mb-2 fh-clamp-2">{{ $merchFandom }}</p>
        @endif

        <div class="fh-card-meta">
            <span class="fh-meta-item fh-price">
                @if($merchPrice !== null)
                    {{ $merchCurrency }} {{ number_format((float) $merchPrice, 2) }}
                @else
                    Price TBC
                @endif
            </span>

            @if($merchTags->isNotEmpty())
                <span class="fh-meta-item fh-clamp-2">
                    {{ $merchTags->take(3)->implode(' · ') }}
                </span>
            @endif
        </div>

        <div class="fh-card-foot">
            <span class="fh-text-dim fh-text-sm">
                <i class="bi bi-eye" aria-hidden="true"></i>
                {{ number_format((int) data_get($item, 'popularity', 0)) }} fans
            </span>
            <a class="fh-card-cta" href="{{ $merchUrl }}">
                View details <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</article>
