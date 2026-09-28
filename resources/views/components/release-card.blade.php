@props(['item'])

@php
    $releaseTitle = (string) data_get($item, 'title');
    $releaseType = data_get($item, 'release_type') ?: 'anime';
    $releaseDate = data_get($item, 'release_date');
    $releaseStatus = data_get($item, 'status') ?: 'upcoming';
    $releaseBookmarked = (bool) data_get($item, 'bookmarked', false);
    $releaseBookmarkId = data_get($item, 'bookmark_id');
    $releaseContentId = data_get($item, 'content_id');

    // There is no releases.show route — link to the related content when the
    // controller preloaded it, otherwise fall back to the releases index.
    $releaseContent = data_get($item, 'content');
    $releaseUrl = ($releaseContent && data_get($releaseContent, 'slug'))
        ? route('content.show', data_get($releaseContent, 'slug'))
        : route('releases.index');

    $releasePoster = img(
        data_get($item, 'poster'),
        $releaseTitle,
        600,
        800,
        data_get($item, 'category.slug')
    );

    $releaseTone = match ($releaseType) {
        'game' => 'green',
        'movie' => 'pink',
        'tv' => 'blue',
        'comic' => 'amber',
        'merchandise' => 'cyan',
        default => 'violet',
    };

    $releaseStatusVariant = match ($releaseStatus) {
        'released' => 'green',
        'delayed' => 'amber',
        default => 'violet',
    };
@endphp

<article {{ $attributes->merge(['class' => 'fh-card fh-card-hover']) }}>
    <div class="fh-card-media fh-media fh-media-3x4">
        <a href="{{ $releaseUrl }}" aria-label="{{ 'Open release '.$releaseTitle }}">
            <img
                class="fh-img"
                src="{{ $releasePoster }}"
                alt="{{ $releaseTitle }}"
                width="600"
                height="800"
                loading="lazy"
                decoding="async"
            >
        </a>

        <div class="fh-card-top-actions">
            <x-bookmark
                type="release"
                :id="data_get($item, 'id')"
                :bookmarked="$releaseBookmarked"
                :bookmark-id="$releaseBookmarkId"
                :note="data_get($item, 'bookmark_note')"
                size="sm"
            />
        </div>
    </div>

    <div class="fh-card-body">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fh-chip fh-chip-{{ $releaseTone }}">
                <i class="bi bi-collection-play" aria-hidden="true"></i>
                {{ ucfirst($releaseType) }}
            </span>
            <x-badge variant="{{ $releaseStatusVariant }}">{{ $releaseStatus }}</x-badge>
        </div>

        <h3 class="fh-card-title fh-clamp-2">
            <a href="{{ $releaseUrl }}">{{ $releaseTitle }}</a>
        </h3>

        <div class="fh-card-meta">
            @if($releaseDate)
                <span class="fh-meta-item">
                    <i class="bi bi-calendar-event" aria-hidden="true"></i>
                    {{ \Illuminate\Support\Carbon::parse($releaseDate)->format('M j, Y') }}
                </span>
            @endif
            <span class="fh-meta-item">
                <i class="bi bi-fire" aria-hidden="true"></i>
                {{ number_format((int) data_get($item, 'popularity', 0)) }}
            </span>
        </div>

        <div class="fh-card-foot">
            <span class="fh-text-dim fh-text-sm fh-clamp-2">
                {{ \Illuminate\Support\Str::limit((string) data_get($item, 'description'), 58) }}
            </span>
            <a class="fh-card-cta" href="{{ $releaseUrl }}">
                {{ $releaseContentId ? 'Details' : 'View all' }}
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</article>
