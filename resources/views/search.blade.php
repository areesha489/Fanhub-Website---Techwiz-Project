@extends('layouts.app')

@section('title', $q !== '' ? 'Search “' . $q . '” — Fan Hub+' : 'Search — Fan Hub+')

@section('content')
    @php
        $tabUrl = function (string $tab) use ($q, $categorySlug, $genre, $year, $sort) {
            return route('search', array_filter([
                'q' => $q,
                'type' => $tab === 'all' ? '' : $tab,
                'category' => $categorySlug,
                'genre' => $genre,
                'year' => $year !== null ? (string) $year : '',
                'sort' => $sort,
            ], fn ($value) => $value !== '' && $value !== null));
        };

        $cardComponents = [
            'content' => 'content-card',
            'article' => 'article-card',
            'character' => 'character-card',
            'media' => 'media-card',
            'event' => 'event-card',
            'merchandise' => 'merch-card',
            'release' => 'release-card',
        ];
        $cardComponent = $cardComponents[$type] ?? 'content-card';
        $typeLabels = [
            'content' => 'Content',
            'article' => 'Articles',
            'character' => 'Characters',
            'media' => 'Multimedia',
            'event' => 'Events',
            'merchandise' => 'Merchandise',
            'release' => 'Releases',
        ];
    @endphp

    <x-page-banner
        kicker="Search"
        title="Find anything"
        subtitle="Titles, articles, characters, media, events, merchandise and releases — all in one place."
        image="{{ asset('images/catalogue/banner-search.png') }}"
    >
        <x-search-bar :action="route('search')" :value="$q" placeholder="What are you looking for?" />
    </x-page-banner>

    {{-- Result header --}}
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">
                @if($q !== '')
                    Results for <span class="fh-text-gradient">“{{ $q }}”</span>
                @elseif($hasQuery)
                    Filtered results
                @else
                    Search Fan Hub+
                @endif
            </h1>
            <p class="fh-page-sub">
                @if($hasQuery)
                    <strong>{{ number_format($total) }}</strong>
                    {{ $total === 1 ? 'result' : 'results' }} found
                    @if($category) in {{ $category->name }}@endif
                    @if($genre !== '') · {{ $genre }}@endif
                    @if($year !== null) · {{ $year }}@endif
                @else
                    Type a query above, or jump straight into a type below.
                @endif
            </p>
        </div>
    </div>

    {{-- Per-type tabs with counts --}}
    <ul class="nav nav-pills flex-wrap gap-2 mb-4" role="list" aria-label="Result types">
        <li class="nav-item">
            <a
                class="nav-link{{ $type === 'all' ? ' active' : '' }}"
                href="{{ $tabUrl('all') }}"
                @if($type === 'all') aria-current="page" @endif
            >
                All <span class="fh-badge fh-badge-slate rounded-pill">{{ number_format(array_sum($counts)) }}</span>
            </a>
        </li>
        @foreach($typeLabels as $typeKey => $typeLabel)
            <li class="nav-item">
                <a
                    class="nav-link{{ $type === $typeKey ? ' active' : '' }}"
                    href="{{ $tabUrl($typeKey) }}"
                    @if($type === $typeKey) aria-current="page" @endif
                >
                    {{ $typeLabel }}
                    <span class="fh-badge fh-badge-slate rounded-pill">{{ number_format($counts[$typeKey] ?? 0) }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Refine filters (kept for guests, submits server-side) --}}
    @if($hasQuery)
        <form action="{{ route('search') }}" method="GET" id="search-filters" class="fh-toolbar">
            <input type="hidden" name="q" value="{{ $q }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <div>
                <label class="fh-label" for="sr-category">Category</label>
                <select class="fh-field" id="sr-category" name="category">
                    <option value="">All categories</option>
                    @foreach($facets['categories'] as $facetCategory)
                        <option value="{{ $facetCategory->slug }}" @selected($categorySlug === $facetCategory->slug)>
                            {{ $facetCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="fh-label" for="sr-year">Year</label>
                <select class="fh-field" id="sr-year" name="year">
                    <option value="">Any year</option>
                    @foreach($facets['years'] as $facetYear)
                        <option value="{{ $facetYear }}" @selected($year === (int) $facetYear)>{{ $facetYear }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="fh-label" for="sr-sort">Sort</label>
                <select class="fh-field" id="sr-sort" name="sort">
                    <option value="relevance" @selected($sort === 'relevance')">Relevance</option>
                    <option value="latest" @selected($sort === 'latest')">Latest</option>
                    <option value="popular" @selected($sort === 'popular')">Popular</option>
                    <option value="date" @selected($sort === 'date')">By date</option>
                </select>
            </div>
            <button class="btn btn-fh btn-sm" type="submit">
                <i class="bi bi-funnel" aria-hidden="true"></i> Refine
            </button>
            @if($categorySlug !== '' || $genre !== '' || $year !== null || $sort !== 'relevance')
                <a class="btn btn-fh-ghost btn-sm" href="{{ $tabUrl('all') }}">
                    <i class="bi bi-x-lg" aria-hidden="true"></i> Clear refinements
                </a>
            @endif
        </form>
    @endif

    {{-- Results --}}
    @if($error)
        <x-error :message="$error" />
    @elseif(! $hasQuery)
        <x-empty
            variant="search"
            title="Search the fandom universe"
            message="Look for a title, a character, an event or a piece of merch — for example “love”, “arcade” or “cosplay”."
            icon="bi-search"
        >
            <a class="btn btn-fh" href="{{ route('explore') }}">Browse the catalogue</a>
        </x-empty>
    @elseif($total === 0)
        <x-empty
            variant="search"
            title="No results for “{{ $q !== '' ? $q : 'those filters' }}”"
            message="Check the spelling, use fewer words, or try a broader term. You can also browse by category."
            icon="bi-search"
        >
            <a class="btn btn-fh" href="{{ route('explore') }}">Explore titles</a>
            <a class="btn btn-fh-ghost" href="{{ $tabUrl('all') }}">Reset refinements</a>
        </x-empty>
    @elseif($type === 'all' && $rows->isNotEmpty())
        <div class="d-flex flex-column gap-3">
            @foreach($rows as $row)
                <article class="fh-card fh-card-hover">
                    <div class="row g-0">
                        <div class="col-4 col-md-3 col-xl-2">
                            <a class="fh-media fh-media-16x9 h-100" href="{{ $row['url'] }}" aria-label="Open {{ $row['title'] }}">
                                <img
                                    class="fh-img"
                                    src="{{ $row['image'] }}"
                                    alt="{{ $row['title'] }}"
                                    width="480"
                                    height="320"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </a>
                        </div>
                        <div class="col-8 col-md-9 col-xl-10">
                            <div class="fh-card-body">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <x-badge variant="violet">{{ $row['type_label'] }}</x-badge>
                                    @if($row['category'])
                                        <a class="fh-chip fh-chip-cyan" href="{{ route('category.show', $row['category_slug']) }}">
                                            {{ $row['category'] }}
                                        </a>
                                    @endif
                                </div>

                                <h2 class="fh-card-title fh-clamp-2 mb-1">
                                    <a href="{{ $row['url'] }}">{{ $row['title'] }}</a>
                                </h2>

                                @if($row['excerpt'] !== '')
                                    <p class="fh-text-muted-2 fh-text-sm fh-clamp-2 mb-2">{{ $row['excerpt'] }}</p>
                                @endif

                                <div class="fh-card-meta">
                                    <span class="fh-meta-item">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                        {{ number_format($row['views']) }}
                                    </span>
                                    @if($row['date'])
                                        <span class="fh-meta-item">
                                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                                            {{ \Illuminate\Support\Carbon::parse($row['date'])->format('M j, Y') }}
                                        </span>
                                    @endif
                                    <a class="fh-card-cta ms-auto" href="{{ $row['url'] }}">
                                        Open <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <x-pagination :paginator="$results" class="mt-4" />
    @elseif($results && $results->count() > 0)
        <div class="fh-grid">
            @foreach($results as $item)
                <x-dynamic-component :component="$cardComponent" :item="$item" />
            @endforeach
        </div>

        <x-pagination :paginator="$results" class="mt-4" />
    @else
        <x-empty
            variant="search"
            title="No {{ strtolower($typeLabels[$type] ?? 'results') }} found"
            message="Nothing in this type matches “{{ $q }}” yet — try another tab or a different query."
            icon="bi-search"
        >
            <a class="btn btn-fh" href="{{ $tabUrl('all') }}">See all results</a>
        </x-empty>
    @endif
@endsection

@section('scripts')
    <script>
        (function () {
            var form = document.getElementById('search-filters');
            if (!form) return;
            form.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', function () { form.submit(); });
            });
        })();
    </script>
@endsection
