@extends('layouts.app')

@section('title', 'Explore — Fan Hub+')

@section('content')
    <x-page-banner
        kicker="Catalogue"
        title="Explore"
        subtitle="Filter every published title by fandom, type, genre and year — then sort it the way you like."
        image="{{ asset('images/catalogue/banner-explore.png') }}"
    >
        <x-search-bar :action="route('search')" :value="$filters['q']" placeholder="Search everything…" />
    </x-page-banner>

    <div class="fh-layout-with-aside">
        {{-- Filter sidebar --}}
        <x-filter-panel :action="route('explore')" title="Filters">
            <input type="hidden" name="view" value="{{ $filters['view'] }}">

            <div class="fh-filter-group">
                <label class="fh-label" for="ex-q">Keyword</label>
                <input
                    class="fh-field"
                    type="search"
                    id="ex-q"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Search titles…"
                    autocomplete="off"
                >
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="ex-category">Category</label>
                <select class="fh-field" id="ex-category" name="category">
                    <option value="">All categories</option>
                    @foreach($facets['categories'] as $facetCategory)
                        <option value="{{ $facetCategory->slug }}" @selected($filters['category'] === $facetCategory->slug)>
                            {{ $facetCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="ex-type">Type</label>
                <select class="fh-field" id="ex-type" name="type">
                    <option value="">All types</option>
                    @foreach($facets['types'] as $facetType)
                        <option value="{{ $facetType }}" @selected($filters['type'] === $facetType)>{{ $facetType }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="ex-genre">Genre</label>
                <select class="fh-field" id="ex-genre" name="genre">
                    <option value="">All genres</option>
                    @foreach($facets['genres'] as $facetGenre)
                        <option value="{{ $facetGenre }}" @selected($filters['genre'] === $facetGenre)>{{ $facetGenre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="ex-year">Release year</label>
                <select class="fh-field" id="ex-year" name="year">
                    <option value="">Any year</option>
                    @foreach($facets['years'] as $facetYear)
                        <option value="{{ $facetYear }}" @selected($filters['year'] === (string) $facetYear)>{{ $facetYear }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="ex-sort">Sort by</label>
                <select class="fh-field" id="ex-sort" name="sort">
                    <option value="latest" @selected($filters['sort'] === 'latest')">Latest first</option>
                    <option value="popular" @selected($filters['sort'] === 'popular')">Most popular</option>
                    <option value="rating" @selected($filters['sort'] === 'rating')">Highest rated</option>
                    <option value="alphabetical" @selected($filters['sort'] === 'alphabetical')">A – Z</option>
                </select>
            </div>

            <button class="btn btn-fh w-100 mt-2" type="submit">
                <i class="bi bi-funnel" aria-hidden="true"></i> Apply filters
            </button>
            <a class="btn btn-fh-ghost w-100 mt-2" href="{{ route('explore', $filters['view'] === 'list' ? ['view' => 'list'] : []) }}">
                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Reset
            </a>
        </x-filter-panel>

        {{-- Results --}}
        <div class="min-w-0">
            @if($error)
                <x-error :message="$error" />
            @else
                @php
                    $baseQuery = array_filter([
                        'q' => $filters['q'],
                        'category' => $filters['category'],
                        'type' => $filters['type'],
                        'genre' => $filters['genre'],
                        'year' => $filters['year'],
                        'sort' => $filters['sort'] === 'latest' ? null : $filters['sort'],
                    ], fn ($value) => $value !== null && $value !== '');
                    $gridUrl = route('explore', array_merge($baseQuery, ['view' => 'grid']));
                    $listUrl = route('explore', array_merge($baseQuery, ['view' => 'list']));
                    $isGrid = $filters['view'] !== 'list';
                    $resultTotal = $results ? $results->total() : 0;
                @endphp

                <div class="fh-toolbar">
                    <div class="min-w-0">
                        <h2 class="h5 mb-1">
                            {{ number_format($resultTotal) }} {{ $resultTotal === 1 ? 'result' : 'results' }}
                            @if($filters['q'] !== '')
                                <span class="fh-text-dim fw-normal">for “{{ $filters['q'] }}”</span>
                            @endif
                        </h2>
                        <p class="fh-text-dim fh-text-sm mb-0">
                            @if($category)
                                {{ $category->name }} ·
                            @endif
                            Sorted by {{ ['latest' => 'latest', 'popular' => 'popularity', 'rating' => 'rating', 'alphabetical' => 'A – Z'][$filters['sort']] }}
                            · {{ $isGrid ? 'Grid' : 'List' }} view
                        </p>
                    </div>

                    <div class="ms-auto d-flex gap-2" role="group" aria-label="Result view">
                        <a
                            class="btn btn-sm {{ $isGrid ? 'btn-fh' : 'btn-fh-ghost' }}"
                            href="{{ $gridUrl }}"
                            aria-label="Grid view"
                            aria-pressed="{{ $isGrid ? 'true' : 'false' }}"
                        >
                            <i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i>
                        </a>
                        <a
                            class="btn btn-sm {{ $isGrid ? 'btn-fh-ghost' : 'btn-fh' }}"
                            href="{{ $listUrl }}"
                            aria-label="List view"
                            aria-pressed="{{ $isGrid ? 'false' : 'true' }}"
                        >
                            <i class="bi bi-list-ul" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                @if($chips !== [])
                    <div class="d-flex flex-wrap gap-2 mb-3" aria-label="Active filters">
                        @foreach($chips as $chip)
                            <a class="fh-chip" href="{{ $chip['href'] }}" aria-label="Remove filter {{ $chip['label'] }}">
                                <i class="bi bi-x-circle" aria-hidden="true"></i>
                                {{ $chip['label'] }}
                            </a>
                        @endforeach
                        <a class="fh-chip fh-chip-slate" href="{{ route('explore', $filters['view'] === 'list' ? ['view' => 'list'] : []) }}">
                            <i class="bi bi-x-lg" aria-hidden="true"></i> Clear all
                        </a>
                    </div>
                @endif

                @if(! $results || $resultTotal === 0)
                    <x-empty
                        variant="search"
                        title="No content found"
                        message="Try changing your filters — or search the whole platform instead."
                        icon="bi-search"
                    >
                        <a class="btn btn-fh" href="{{ route('explore') }}">Clear all filters</a>
                        <a class="btn btn-fh-ghost" href="{{ route('search') }}">Global search</a>
                    </x-empty>
                @elseif($results->count() === 0)
                    <x-empty
                        title="Nothing on this page"
                        message="This page sits past the last result — jump back to the first page."
                        icon="bi-arrow-left-circle"
                    >
                        <a class="btn btn-fh" href="{{ route('explore', array_merge($baseQuery, ['view' => $filters['view']])) }}">
                            Back to first page
                        </a>
                    </x-empty>
                @elseif($isGrid)
                    <div class="fh-grid">
                        @foreach($results as $item)
                            <x-content-card :item="$item" />
                        @endforeach
                    </div>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($results as $item)
                            @php
                                $itemCategory = $item->category;
                                $itemUrl = route('content.show', $item->slug);
                            @endphp
                            <article class="fh-card fh-card-hover">
                                <div class="row g-0">
                                    <div class="col-4 col-md-3">
                                        <a class="fh-media fh-media-16x9 h-100" href="{{ $itemUrl }}" aria-label="Open {{ $item->title }}">
                                            <img
                                                class="fh-img"
                                                src="{{ img($item->cover_image, $item->title, 640, 360, $itemCategory?->slug) }}"
                                                alt="{{ $item->title }}"
                                                width="640"
                                                height="360"
                                                loading="lazy"
                                                decoding="async"
                                            >
                                        </a>
                                    </div>
                                    <div class="col-8 col-md-9">
                                        <div class="fh-card-body">
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                @if($itemCategory)
                                                    <a class="fh-chip" href="{{ route('category.show', $itemCategory->slug) }}">
                                                        <i class="bi {{ $itemCategory->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                                                        {{ $itemCategory->name }}
                                                    </a>
                                                @endif
                                                <span class="fh-chip fh-chip-slate">{{ $item->content_type }}</span>
                                                <span class="ms-auto">
                                                    <x-bookmark
                                                        type="content"
                                                        :id="$item->id"
                                                        :bookmarked="(bool) $item->bookmarked"
                                                        :bookmark-id="$item->bookmark_id ?? null"
                                                        size="sm"
                                                    />
                                                </span>
                                            </div>

                                            <h3 class="fh-card-title fh-clamp-2 mb-1">
                                                <a href="{{ $itemUrl }}">{{ $item->title }}</a>
                                            </h3>

                                            <p class="fh-text-muted-2 fh-text-sm fh-clamp-2 mb-2">
                                                {{ \Illuminate\Support\Str::limit(strip_tags((string) ($item->synopsis ?: $item->description)), 160) }}
                                            </p>

                                            <div class="fh-card-meta">
                                                <span class="fh-meta-item">
                                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                                    {{ number_format((int) $item->views_count) }}
                                                </span>
                                                @if($item->rating > 0)
                                                    <x-rating :value="$item->rating" size="sm" />
                                                @endif
                                                @if($item->release_date)
                                                    <span class="fh-meta-item">
                                                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                                                        {{ $item->release_date->format('M Y') }}
                                                    </span>
                                                @endif
                                                <span class="fh-meta-item">
                                                    <i class="bi bi-tag" aria-hidden="true"></i>
                                                    {{ $item->genre ?: 'Various' }}
                                                </span>
                                                <a class="fh-card-cta ms-auto" href="{{ $itemUrl }}">
                                                    Details <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                <x-pagination :paginator="$results" class="mt-4" />
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var form = document.getElementById('x-filters-form');
            if (!form) return;
            form.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', function () { form.submit(); });
            });
        })();
    </script>
@endsection
