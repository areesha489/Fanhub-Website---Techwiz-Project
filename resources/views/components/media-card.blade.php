@props(['item'])

@php
    $mediaTitle = (string) data_get($item, 'title');
    $mediaSlug = data_get($item, 'slug');
    $mediaCategory = data_get($item, 'category');
    $mediaType = data_get($item, 'media_type') ?: 'video';
    $mediaViews = (int) data_get($item, 'views_count', 0);
    $mediaRating = (float) data_get($item, 'rating', 0);
    $mediaDuration = data_get($item, 'duration');
    $mediaBookmarked = (bool) data_get($item, 'bookmarked', false);
    $mediaBookmarkId = data_get($item, 'bookmark_id');
    $mediaUrl = route('media.show', $mediaSlug);
    $mediaThumb = img(
        data_get($item, 'thumbnail'),
        $mediaTitle,
        800,
        450,
        data_get($mediaCategory, 'slug')
    );

    $mediaTypeMap = [
        'video' => ['label' => 'Video', 'icon' => 'bi-play-circle-fill', 'tone' => 'violet'],
        'trailer' => ['label' => 'Trailer', 'icon' => 'bi-film', 'tone' => 'pink'],
        'audio' => ['label' => 'Audio', 'icon' => 'bi-music-note-beamed', 'tone' => 'cyan'],
        'gallery' => ['label' => 'Gallery', 'icon' => 'bi-images', 'tone' => 'amber'],
    ];

    $mediaMeta = $mediaTypeMap[$mediaType] ?? $mediaTypeMap['video'];
@endphp

<article {{ $attributes->merge(['class' => 'fh-card fh-card-hover']) }}>
    <div class="fh-card-media fh-media-card fh-media fh-media-16x9">
        <a href="{{ $mediaUrl }}" aria-label="{{ 'Open '.$mediaTitle }}">
            <img
                class="fh-img"
                src="{{ $mediaThumb }}"
                alt="{{ $mediaTitle }}"
                width="800"
                height="450"
                loading="lazy"
                decoding="async"
            >
        </a>

        <span class="fh-media-play" aria-hidden="true">
            <i class="bi {{ $mediaMeta['icon'] }}"></i>
        </span>

        @if($mediaDuration)
            <span class="fh-media-duration">{{ $mediaDuration }}</span>
        @endif

        <div class="fh-card-top-actions">
            <x-bookmark
                type="media"
                :id="data_get($item, 'id')"
                :bookmarked="$mediaBookmarked"
                :bookmark-id="$mediaBookmarkId"
                :note="data_get($item, 'bookmark_note')"
                size="sm"
            />
        </div>
    </div>

    <div class="fh-card-body">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fh-chip fh-chip-{{ $mediaMeta['tone'] }}">
                <i class="bi {{ $mediaMeta['icon'] }}" aria-hidden="true"></i>
                {{ $mediaMeta['label'] }}
            </span>

            @if($mediaCategory)
                <a class="fh-chip fh-chip-slate" href="{{ route('category.show', data_get($mediaCategory, 'slug')) }}">
                    {{ data_get($mediaCategory, 'name') }}
                </a>
            @endif
        </div>

        <h3 class="fh-card-title fh-clamp-2">
            <a href="{{ $mediaUrl }}">{{ $mediaTitle }}</a>
        </h3>

        <div class="fh-card-meta">
            <span class="fh-meta-item">
                <i class="bi bi-eye" aria-hidden="true"></i>
                {{ number_format($mediaViews) }}
            </span>

            @if($mediaRating > 0)
                <x-rating :value="$mediaRating" size="sm" />
            @endif
        </div>

        <div class="fh-card-foot">
            <span class="fh-text-dim fh-text-sm fh-clamp-2">
                {{ \Illuminate\Support\Str::limit((string) data_get($item, 'description'), 60) }}
            </span>
            <a class="fh-card-cta" href="{{ $mediaUrl }}">
                Watch <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</article>
