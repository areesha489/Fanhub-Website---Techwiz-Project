@props(['item'])

@php
    $characterName = (string) data_get($item, 'name');
    $characterSlug = data_get($item, 'slug');
    $characterCategory = data_get($item, 'category');
    $characterFandom = data_get($item, 'fandom') ?: data_get($characterCategory, 'name');
    $characterViews = (int) data_get($item, 'views_count', 0);
    $characterBookmarked = (bool) data_get($item, 'bookmarked', false);
    $characterBookmarkId = data_get($item, 'bookmark_id');
    $characterUrl = route('characters.show', $characterSlug);
    $characterImage = img(
        data_get($item, 'image'),
        $characterName,
        600,
        800,
        data_get($characterCategory, 'slug')
    );
@endphp

<article {{ $attributes->merge(['class' => 'fh-card fh-card-hover']) }}>
    <div class="fh-card-media fh-media fh-media-3x4">
        <a href="{{ $characterUrl }}" aria-label="{{ 'Open profile of '.$characterName }}">
            <img
                class="fh-img"
                src="{{ $characterImage }}"
                alt="{{ $characterName }}"
                width="600"
                height="800"
                loading="lazy"
                decoding="async"
            >
        </a>

        <div class="fh-card-top-actions">
            <x-bookmark
                type="character"
                :id="data_get($item, 'id')"
                :bookmarked="$characterBookmarked"
                :bookmark-id="$characterBookmarkId"
                :note="data_get($item, 'bookmark_note')"
                size="sm"
            />
        </div>

        @if(data_get($item, 'featured'))
            <span class="fh-poster-rating">
                <i class="bi bi-star-fill" aria-hidden="true"></i> Featured
            </span>
        @endif
    </div>

    <div class="fh-card-body">
        @if($characterCategory)
            <a class="fh-chip" href="{{ route('category.show', data_get($characterCategory, 'slug')) }}">
                <i class="bi {{ data_get($characterCategory, 'icon') ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                {{ data_get($characterCategory, 'name') }}
            </a>
        @endif

        <h3 class="fh-card-title fh-clamp-2">
            <a href="{{ $characterUrl }}">{{ $characterName }}</a>
        </h3>

        @if($characterFandom)
            <p class="fh-text-muted-2 fh-text-sm mb-2 fh-clamp-2">{{ $characterFandom }}</p>
        @endif

        <div class="fh-card-meta">
            <span class="fh-meta-item">
                <i class="bi bi-eye" aria-hidden="true"></i>
                {{ number_format($characterViews) }}
            </span>
            @if(data_get($item, 'affiliation'))
                <span class="fh-meta-item fh-clamp-2">
                    <i class="bi bi-people" aria-hidden="true"></i>
                    {{ data_get($item, 'affiliation') }}
                </span>
            @endif
        </div>

        <div class="fh-card-foot">
            <span class="fh-text-dim fh-text-sm">{{ data_get($item, 'gender') ?: 'Character' }}</span>
            <a class="fh-card-cta" href="{{ $characterUrl }}">
                Details <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</article>
