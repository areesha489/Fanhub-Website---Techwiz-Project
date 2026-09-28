@props(['item'])

@php
    $articleTitle = (string) data_get($item, 'title');
    $articleSlug = data_get($item, 'slug');
    $articleCategory = data_get($item, 'category');
    $articleAuthor = data_get($item, 'author');
    $articleViews = (int) data_get($item, 'views_count', 0);
    $articleReadingTime = (int) data_get($item, 'reading_time', 5);
    $articlePublished = data_get($item, 'published_at') ?: data_get($item, 'created_at');
    $articleBookmarked = (bool) data_get($item, 'bookmarked', false);
    $articleBookmarkId = data_get($item, 'bookmark_id');
    $articleUrl = route('articles.show', $articleSlug);
    $articleCover = img(
        data_get($item, 'cover_image'),
        $articleTitle,
        800,
        450,
        data_get($articleCategory, 'slug')
    );
@endphp

<article {{ $attributes->merge(['class' => 'fh-card fh-card-hover']) }}>
    <div class="fh-card-media fh-media fh-media-16x9">
        <a href="{{ $articleUrl }}" aria-label="{{ 'Read '.$articleTitle }}">
            <img
                class="fh-img"
                src="{{ $articleCover }}"
                alt="{{ $articleTitle }}"
                width="800"
                height="450"
                loading="lazy"
                decoding="async"
            >
        </a>

        <div class="fh-card-top-actions">
            <x-bookmark
                type="article"
                :id="data_get($item, 'id')"
                :bookmarked="$articleBookmarked"
                :bookmark-id="$articleBookmarkId"
                :note="data_get($item, 'bookmark_note')"
                size="sm"
            />
        </div>
    </div>

    <div class="fh-card-body">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
            @if($articleCategory)
                <a class="fh-chip fh-chip-cyan" href="{{ route('category.show', data_get($articleCategory, 'slug')) }}">
                    <i class="bi {{ data_get($articleCategory, 'icon') ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                    {{ data_get($articleCategory, 'name') }}
                </a>
            @endif
        </div>

        <h3 class="fh-card-title fh-clamp-2">
            <a href="{{ $articleUrl }}">{{ $articleTitle }}</a>
        </h3>

        <div class="fh-card-meta mb-2">
            <span class="fh-meta-item">
                <i class="bi bi-person-circle" aria-hidden="true"></i>
                {{ data_get($articleAuthor, 'name') ?: 'Fan Hub+ staff' }}
            </span>
            @if($articlePublished)
                <span class="fh-meta-item">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    {{ \Illuminate\Support\Carbon::parse($articlePublished)->format('M j, Y') }}
                </span>
            @endif
        </div>

        <div class="fh-card-foot">
            <span class="fh-meta-item fh-text-dim fh-text-sm">
                <i class="bi bi-clock" aria-hidden="true"></i>
                {{ $articleReadingTime }} min read
            </span>
            <span class="fh-meta-item fh-text-dim fh-text-sm">
                <i class="bi bi-eye" aria-hidden="true"></i>
                {{ number_format($articleViews) }}
            </span>
            <a class="fh-card-cta" href="{{ $articleUrl }}">
                Read <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</article>
