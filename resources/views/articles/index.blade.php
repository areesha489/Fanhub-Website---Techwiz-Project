@extends('layouts.app')

@section('title', 'Articles · Fan Hub+')

@section('styles')
<style>
    .fh-chip.is-active {
        background: rgba(34, 211, 238, .18);
        border-color: rgba(34, 211, 238, .45);
        color: #67e8f9;
    }
    .fh-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: 1.35rem;
    }
    .fh-sort-form {
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .fh-hero-title-link {
        color: inherit;
        text-decoration: none;
    }
    .fh-hero-title-link:hover {
        color: var(--fh-violet-2);
    }
</style>
@endsection

@section('content')
<x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Articles']]" />

<x-page-banner
    kicker="From the editorial desk"
    title="Articles"
    subtitle="Reviews, retrospectives and convention dispatches written by fans, for fans — pick a piece and settle in."
    image="{{ asset('images/catalogue/banner-articles.png') }}"
>
    @if($error === null)
        <span class="fh-badge fh-badge-cyan">{{ number_format($publishedCount) }} published</span>
    @endif
</x-page-banner>

@if($error !== null)
    <x-error :message="$error">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('articles.index') }}">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Try again
        </a>
    </x-error>
@else
    @php
        $activeFilters = array_filter([
            'q' => $search,
            'category' => $category,
            'sort' => $sort !== 'latest' ? $sort : '',
        ], fn ($value) => $value !== '');
    @endphp

    <div class="fh-toolbar">
        <form class="fh-search" role="search" action="{{ route('articles.index') }}" method="get">
            <label class="visually-hidden" for="articles-search">Search articles</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                type="search"
                id="articles-search"
                name="q"
                value="{{ $search }}"
                placeholder="Search titles, excerpts and stories…"
                autocomplete="off"
            >
            @if($category !== '')
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <input type="hidden" name="sort" value="{{ $sort }}">
            <button class="btn btn-fh btn-sm" type="submit">
                <span class="d-none d-sm-inline">Search</span>
                <i class="bi bi-search d-sm-none" aria-hidden="true"></i>
            </button>
        </form>

        <form class="fh-sort-form" action="{{ route('articles.index') }}" method="get">
            @if($search !== '')
                <input type="hidden" name="q" value="{{ $search }}">
            @endif
            @if($category !== '')
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <label class="fh-label mb-0" for="articles-sort">Sort</label>
            <select class="fh-field" id="articles-sort" name="sort" style="width:auto" onchange="this.form.submit()">
                <option value="latest" @selected($sort === 'latest')>Newest first</option>
                <option value="popular" @selected($sort === 'popular')>Most read</option>
                <option value="reading_time" @selected($sort === 'reading_time')>Quickest reads</option>
            </select>
            <button class="btn btn-fh-outline btn-sm d-none d-lg-inline-flex" type="submit">Apply</button>
        </form>
    </div>

    <nav class="fh-chip-row" aria-label="Filter articles by category">
        <a
            class="fh-chip {{ $category === '' ? 'is-active' : '' }}"
            href="{{ route('articles.index', array_filter(['q' => $search, 'sort' => $sort], fn ($value) => $value !== '')) }}"
            @if($category === '') aria-current="true" @endif
        >
            <i class="bi bi-collection-fill" aria-hidden="true"></i> All categories
        </a>

        @foreach($categories as $categoryOption)
            <a
                class="fh-chip {{ $category === $categoryOption->slug ? 'is-active' : '' }}"
                href="{{ route('articles.index', array_filter(['q' => $search, 'category' => $categoryOption->slug, 'sort' => $sort], fn ($value) => $value !== '')) }}"
                @if($category === $categoryOption->slug) aria-current="true" @endif
            >
                <i class="bi {{ $categoryOption->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                {{ $categoryOption->name }}
            </a>
        @endforeach
    </nav>

    @if($hero !== null)
        <article class="fh-card fh-card-hover mb-4">
            <div class="row g-0">
                <div class="col-12 col-md-6">
                    <div class="fh-media fh-media-16x9 h-100">
                        <a href="{{ route('articles.show', $hero->slug) }}" aria-label="Read {{ $hero->title }}">
                            <img
                                class="fh-img"
                                src="{{ img($hero->cover_image, $hero->title, 1200, 675, $hero->category?->slug) }}"
                                alt="Cover artwork for {{ $hero->title }}"
                                width="1200"
                                height="675"
                                decoding="async"
                            >
                        </a>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="fh-card-body p-4 d-flex flex-column h-100 justify-content-center">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <x-badge variant="violet">Featured</x-badge>
                            @if($hero->category)
                                <a class="fh-chip fh-chip-cyan" href="{{ route('category.show', $hero->category->slug) }}">
                                    <i class="bi {{ $hero->category->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                                    {{ $hero->category->name }}
                                </a>
                            @endif
                        </div>

                        <h2 class="fh-title mb-2">
                            <a class="fh-hero-title-link" href="{{ route('articles.show', $hero->slug) }}">{{ $hero->title }}</a>
                        </h2>

                        <p class="fh-text-muted-2 mb-3">
                            {{ $hero->excerpt ?: \Illuminate\Support\Str::limit(trim(strip_tags($hero->body)), 190) }}
                        </p>

                        <div class="fh-card-meta mb-3">
                            <span class="fh-meta-item">
                                <x-avatar :user="$hero->author" size="sm" />
                                {{ $hero->author?->name ?: 'Fan Hub+ staff' }}
                            </span>
                            <span class="fh-meta-item">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                {{ ($hero->published_at ?? $hero->created_at)->format('M j, Y') }}
                            </span>
                            <span class="fh-meta-item">
                                <i class="bi bi-clock" aria-hidden="true"></i>
                                {{ (int) $hero->reading_time }} min read
                            </span>
                            <span class="fh-meta-item">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                                {{ number_format((int) $hero->views_count) }}
                            </span>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <a class="btn btn-fh btn-sm" href="{{ route('articles.show', $hero->slug) }}">
                                Read article <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                            <x-bookmark
                                type="article"
                                :id="$hero->id"
                                :bookmarked="(bool) data_get($hero, 'bookmarked', false)"
                                :bookmark-id="data_get($hero, 'bookmark_id')"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </article>
    @endif

    @if($articles->isEmpty())
        @if($publishedCount === 0)
            <x-empty
                title="No articles published yet"
                message="The editors are still putting the first pieces together. Reviews, guides and convention dispatches will appear here the moment they go live."
                icon="bi-newspaper"
            >
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('explore') }}">
                    <i class="bi bi-compass" aria-hidden="true"></i> Explore the catalogue
                </a>
            </x-empty>
        @elseif($activeFilters !== [])
            <x-empty
                title="No articles match those filters"
                message="Try another search term or clear the filters to browse the whole archive."
                icon="bi-search"
            >
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('articles.index') }}">
                    <i class="bi bi-x-circle" aria-hidden="true"></i> Clear filters
                </a>
            </x-empty>
        @else
            <x-empty
                title="You have reached the end"
                message="That is everything published so far — the next feature is already being drafted."
                icon="bi-check2-circle"
            >
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('articles.index') }}">
                    <i class="bi bi-arrow-up" aria-hidden="true"></i> Back to the top
                </a>
            </x-empty>
        @endif
    @else
        <x-section
            :title="$hero !== null ? 'More stories' : 'All stories'"
            :subtitle="number_format($total).' pieces in this view'"
        >
            <div class="fh-grid fh-grid-wide">
                @foreach($articles as $article)
                    <x-article-card :item="$article" />
                @endforeach
            </div>

            <x-pagination :paginator="$articles" />
        </x-section>
    @endif
@endif
@endsection
