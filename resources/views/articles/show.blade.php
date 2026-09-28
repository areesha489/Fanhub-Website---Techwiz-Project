@extends('layouts.app')

@section('title', $article?->title ? $article->title.' · Articles · Fan Hub+' : 'Articles · Fan Hub+')

@section('styles')
<style>
    .fh-article-body {
        max-width: 72ch;
        font-size: 1.045rem;
        line-height: 1.8;
        color: var(--fh-text-2);
    }

    .fh-article-body > *:first-child {
        margin-top: 0;
    }

    .fh-article-body p {
        margin-bottom: 1.15rem;
    }

    .fh-article-body h2,
    .fh-article-body h3,
    .fh-article-body h4 {
        font-family: var(--fh-font-display);
        color: var(--fh-text);
        line-height: 1.3;
        margin: 1.9rem 0 .75rem;
    }

    .fh-article-body h2 { font-size: 1.5rem; }
    .fh-article-body h3 { font-size: 1.22rem; }
    .fh-article-body h4 { font-size: 1.05rem; }

    .fh-article-body a {
        color: var(--fh-violet-2);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .fh-article-body a:hover {
        color: #c4b5fd;
    }

    .fh-article-body ul,
    .fh-article-body ol {
        padding-left: 1.3rem;
        margin-bottom: 1.15rem;
    }

    .fh-article-body li {
        margin-bottom: .4rem;
    }

    .fh-article-body blockquote {
        border-left: 3px solid var(--fh-violet);
        background: rgba(139, 92, 246, .08);
        padding: .9rem 1.15rem;
        border-radius: var(--fh-radius-sm);
        margin: 1.5rem 0;
        color: var(--fh-text);
        font-style: italic;
    }

    .fh-article-body img {
        max-width: 100%;
        height: auto;
        border-radius: var(--fh-radius);
        margin: 1rem 0;
    }

    .fh-article-body figure {
        margin: 1.5rem 0;
    }

    .fh-article-body figcaption {
        color: var(--fh-dim);
        font-size: .86rem;
        margin-top: .45rem;
    }

    .fh-article-body pre {
        background: #060b18;
        border: 1px solid var(--fh-border);
        padding: 1rem;
        border-radius: var(--fh-radius-sm);
        overflow-x: auto;
        margin-bottom: 1.15rem;
    }

    .fh-article-body :not(pre) > code {
        color: #f0abfc;
    }

    .fh-article-body hr {
        border: 0;
        border-top: 1px solid var(--fh-border);
        margin: 1.9rem 0;
    }

    .fh-article-body table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.3rem;
        display: block;
        overflow-x: auto;
    }

    .fh-article-body th,
    .fh-article-body td {
        border-bottom: 1px solid var(--fh-border);
        padding: .55rem .65rem;
        text-align: left;
    }

    .fh-article-body strong {
        color: var(--fh-text);
    }
</style>
@endsection

@section('content')
@if($loadError !== null || $article === null)
    <x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Articles', 'to' => route('articles.index')]]" />
    <x-error :message="$loadError ?? 'We could not load this article right now. Please try again in a moment. The piece you followed may have been unpublished.'">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('articles.index') }}">
            <i class="bi bi-newspaper" aria-hidden="true"></i> Back to articles
        </a>
    </x-error>
@else
    @php
        $articleCategory = $article->category;
        $publishedAt = $article->published_at ?? $article->created_at;
    @endphp

    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Articles', 'to' => route('articles.index')],
        ['label' => $article->title],
    ]" />

    <article class="fh-card fh-card-hover mb-4">
        <div class="fh-media fh-media-16x9">
            <img
                class="fh-img"
                src="{{ img($article->cover_image, $article->title, 1280, 720, $articleCategory?->slug) }}"
                alt="Cover artwork for {{ $article->title }}"
                width="1280"
                height="720"
                decoding="async"
            >
        </div>

        <div class="fh-card-body p-4 p-lg-5">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                @if($articleCategory)
                    <a class="fh-chip fh-chip-cyan" href="{{ route('category.show', $articleCategory->slug) }}">
                        <i class="bi {{ $articleCategory->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                        {{ $articleCategory->name }}
                    </a>
                @endif

                @if($article->featured)
                    <x-badge variant="violet">Featured</x-badge>
                @endif
            </div>

            <h1 class="fh-title mb-3" style="font-size: clamp(1.7rem, 3.6vw, 2.6rem);">{{ $article->title }}</h1>

            @if($article->excerpt)
                <p class="fh-sub mb-3" style="max-width: 72ch;">{{ $article->excerpt }}</p>
            @endif

            <div class="fh-card-meta mb-4">
                <span class="fh-meta-item">
                    <x-avatar :user="$article->author" size="sm" />
                    {{ $article->author?->name ?: 'Fan Hub+ staff' }}
                </span>
                <span class="fh-meta-item">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    {{ $publishedAt->format('F j, Y') }}
                </span>
                <span class="fh-meta-item">
                    <i class="bi bi-clock" aria-hidden="true"></i>
                    {{ (int) $article->reading_time }} min read
                </span>
                <span class="fh-meta-item">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                    {{ number_format((int) $article->views_count) }} views
                </span>
            </div>

            {{-- Admin-authored, trusted HTML from the editorial backend. --}}
            <div class="fh-article-body">
                {!! $article->body !!}
            </div>

            <hr class="fh-divider">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <x-bookmark
                        type="article"
                        :id="$article->id"
                        :bookmarked="$isBookmarked"
                        :bookmark-id="$bookmarkId"
                    />
                    <button
                        type="button"
                        class="btn btn-fh-outline btn-sm"
                        data-share-copy
                        aria-label="Share this article"
                    >
                        <i class="bi bi-share" aria-hidden="true"></i> Share
                    </button>
                </div>

                <span class="fh-text-dim fh-text-sm">
                    <i class="bi bi-journal-bookmark" aria-hidden="true"></i>
                    Published {{ $publishedAt->format('M j, Y') }}
                </span>
            </div>
        </div>
    </article>

    <section class="fh-card fh-card-pad mb-4">
        <div class="d-flex align-items-center gap-3">
            <x-avatar :user="$article->author" size="lg" />
            <div class="min-w-0">
                <p class="fh-eyebrow mb-1">Written by</p>
                <h2 class="h5 mb-1">{{ $article->author?->name ?: 'Fan Hub+ staff' }}</h2>
                <p class="fh-text-muted-2 fh-text-sm mb-0">
                    {{ $article->author?->bio ?: 'Part of the Fan Hub+ editorial crew covering anime, gaming and everything between the panels.' }}
                </p>
            </div>
        </div>
    </section>

    @if($related->isNotEmpty())
        <x-section
            title="Related Articles"
            subtitle="More reading from the same corner of the fandom"
            :href="$articleCategory ? route('articles.index', ['category' => $articleCategory->slug]) : route('articles.index')"
            linkText="View all"
        >
            <div class="fh-grid fh-grid-wide">
                @foreach($related as $relatedArticle)
                    <x-article-card :item="$relatedArticle" />
                @endforeach
            </div>
        </x-section>
    @endif
@endif
@endsection

@section('scripts')
<script>
    document.querySelectorAll('[data-share-copy]').forEach(function (button) {
        button.addEventListener('click', async function () {
            var url = window.location.href;
            try {
                if (navigator.share) {
                    await navigator.share({ title: document.title, url: url });
                    return;
                }
                await navigator.clipboard.writeText(url);
                if (window.FanHub && FanHub.toast) FanHub.toast('success', 'Link copied to your clipboard.');
            } catch (error) {
                if (window.FanHub && FanHub.toast) FanHub.toast('info', 'Copy this link to share: ' + url);
            }
        });
    });
</script>
@endsection
