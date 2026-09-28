@extends('layouts.app')

@section('title', $category->name . ' — Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Categories'],
        ['label' => $category->name],
    ]" />

    @php
        $accent = preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $category->accent) === 1
            ? $category->accent
            : '#8B5CF6';
        $bannerImage = img($category->image, $category->name, 1600, 520, $category->slug);
        $isList = false;
    @endphp

    {{-- Cinematic category banner --}}
    <header
        class="fh-card mb-4"
        style="
            position: relative;
            overflow: hidden;
            background-image: linear-gradient(100deg, rgba(5, 8, 22, 0.94) 0%, rgba(5, 8, 22, 0.82) 42%, rgba(5, 8, 22, 0.35) 100%), url('{{ $bannerImage }}');
            min-height: 240px;
            background-size: cover;
            background-position: center;
            border-top: 3px solid {{ $accent }};
        "
    >
        <div style="padding: clamp(1.4rem, 4vw, 2.6rem);">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                <span
                    class="fh-icon-tile"
                    style="width: 44px; height: 44px; background: linear-gradient(135deg, {{ $accent }}, {{ $accent }}99); box-shadow: 0 10px 24px {{ $accent }}45;"
                    aria-hidden="true"
                >
                    <i class="bi {{ $category->icon ?: 'bi-collection-fill' }}"></i>
                </span>
                <span class="fh-eyebrow mb-0">Fandom category</span>
                <x-badge variant="violet">{{ number_format($stats['contents']) }} {{ $stats['contents'] === 1 ? 'title' : 'titles' }}</x-badge>
            </div>

            <h1 class="fh-page-title mb-2" style="color: #fff;">{{ $category->name }}</h1>
            <p class="fh-page-sub mb-3" style="max-width: 70ch;">
                {{ $category->description ?: 'Everything from the '.$category->name.' fandom — titles, characters, articles, media and release dates in one place.' }}
            </p>

            <div class="d-flex flex-wrap gap-2">
                <span class="fh-chip"><i class="bi bi-film" aria-hidden="true"></i> {{ number_format($stats['contents']) }} titles</span>
                <span class="fh-chip fh-chip-cyan"><i class="bi bi-people" aria-hidden="true"></i> {{ number_format($stats['characters']) }} characters</span>
                <span class="fh-chip fh-chip-magenta"><i class="bi bi-newspaper" aria-hidden="true"></i> {{ number_format($stats['articles']) }} articles</span>
                <span class="fh-chip fh-chip-amber"><i class="bi bi-play-circle" aria-hidden="true"></i> {{ number_format($stats['media']) }} media</span>
                <span class="fh-chip fh-chip-green"><i class="bi bi-calendar-event" aria-hidden="true"></i> {{ number_format($stats['releases']) }} releases</span>
            </div>
        </div>
    </header>

    @if($error)
        <x-error :message="$error" />
    @endif

    {{-- Section tabs (client-side, no reload) --}}
    <ul class="nav nav-tabs mb-4" role="tablist" aria-label="{{ $category->name }} sections">
        @foreach([
            'overview' => 'Overview',
            'featured' => 'Featured',
            'trending' => 'Trending',
            'characters' => 'Characters',
            'articles' => 'Articles',
            'multimedia' => 'Multimedia',
            'releases' => 'Releases',
        ] as $paneId => $paneLabel)
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link{{ $paneId === 'overview' ? ' active' : '' }}"
                    id="tab-{{ $paneId }}"
                    data-bs-toggle="tab"
                    data-bs-target="#pane-{{ $paneId }}"
                    type="button"
                    role="tab"
                    aria-controls="pane-{{ $paneId }}"
                    aria-selected="{{ $paneId === 'overview' ? 'true' : 'false' }}"
                >{{ $paneLabel }}</button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        {{-- Overview: description, quick stats, filtered catalogue --}}
        <div class="tab-pane fade show active" id="pane-overview" role="tabpanel" aria-labelledby="tab-overview" tabindex="0">
            <div class="fh-grid mb-4">
                <x-stat-card label="Published titles" :value="number_format($stats['contents'])" icon="bi-collection-fill" tone="violet" />
                <x-stat-card label="Characters" :value="number_format($stats['characters'])" icon="bi-people" tone="cyan" />
                <x-stat-card label="Articles" :value="number_format($stats['articles'])" icon="bi-newspaper" tone="pink" />
                <x-stat-card label="Media" :value="number_format($stats['media'])" icon="bi-play-circle" tone="amber" />
            </div>

            <div class="fh-toolbar">
                <form action="{{ route('category.show', $category->slug) }}" method="GET" id="cat-filters" class="d-flex flex-wrap align-items-end gap-2 w-100">
                    <div>
                        <label class="fh-label" for="cat-type">Type</label>
                        <select class="fh-field" id="cat-type" name="type">
                            <option value="">All types</option>
                            @foreach($types as $typeOption)
                                <option value="{{ $typeOption }}" @selected($filters['type'] === $typeOption)>{{ $typeOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="fh-label" for="cat-genre">Genre</label>
                        <select class="fh-field" id="cat-genre" name="genre">
                            <option value="">All genres</option>
                            @foreach($genres as $genreOption)
                                <option value="{{ $genreOption }}" @selected($filters['genre'] === $genreOption)>{{ $genreOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="fh-label" for="cat-year">Year</label>
                        <select class="fh-field" id="cat-year" name="year">
                            <option value="">Any year</option>
                            @foreach($years as $yearOption)
                                <option value="{{ $yearOption }}" @selected($filters['year'] === (string) $yearOption)>{{ $yearOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="fh-label" for="cat-sort">Sort</label>
                        <select class="fh-field" id="cat-sort" name="sort">
                            <option value="latest" @selected($filters['sort'] === 'latest')">Latest first</option>
                            <option value="popular" @selected($filters['sort'] === 'popular')">Most popular</option>
                            <option value="rating" @selected($filters['sort'] === 'rating')">Highest rated</option>
                            <option value="alphabetical" @selected($filters['sort'] === 'alphabetical')">A – Z</option>
                        </select>
                    </div>
                    <button class="btn btn-fh btn-sm" type="submit">
                        <i class="bi bi-funnel" aria-hidden="true"></i> Apply
                    </button>
                </form>
            </div>

            @if($contents === null)
                <x-error message="This catalogue could not be loaded right now." />
            @elseif($contents->count() === 0)
                <x-empty
                    title="No titles match"
                    message="Nothing in this category matches the current filters — try a different type, genre or year."
                    icon="bi-film"
                >
                    <a class="btn btn-fh" href="{{ route('category.show', $category->slug) }}">Clear filters</a>
                </x-empty>
            @else
                <div class="fh-grid">
                    @foreach($contents as $item)
                        <x-content-card :item="$item" />
                    @endforeach
                </div>
                <x-pagination :paginator="$contents" class="mt-4" />
            @endif
        </div>

        {{-- Featured --}}
        <div class="tab-pane fade" id="pane-featured" role="tabpanel" aria-labelledby="tab-featured" tabindex="0">
            @if($featured->isEmpty())
                <x-empty
                    title="Nothing featured yet"
                    message="Editor picks from {{ $category->name }} will be highlighted here."
                    icon="bi-star"
                />
            @else
                <div class="fh-grid">
                    @foreach($featured as $item)
                        <x-content-card :item="$item" />
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Trending --}}
        <div class="tab-pane fade" id="pane-trending" role="tabpanel" aria-labelledby="tab-trending" tabindex="0">
            @if($trending->isEmpty())
                <x-empty
                    title="Nothing trending yet"
                    message="Popular {{ $category->name }} titles will be ranked here."
                    icon="bi-fire"
                />
            @else
                <div class="fh-grid">
                    @foreach($trending as $item)
                        <x-content-card :item="$item" />
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Characters --}}
        <div class="tab-pane fade" id="pane-characters" role="tabpanel" aria-labelledby="tab-characters" tabindex="0">
            @if($characters->isEmpty())
                <x-empty
                    title="No characters yet"
                    message="Heroes, rivals and supporting casts from {{ $category->name }} will appear here."
                    icon="bi-people"
                />
            @else
                <div class="fh-grid fh-character-grid">
                    @foreach($characters as $item)
                        <x-character-card :item="$item" />
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Articles --}}
        <div class="tab-pane fade" id="pane-articles" role="tabpanel" aria-labelledby="tab-articles" tabindex="0">
            @if($articles->isEmpty())
                <x-empty
                    title="No articles yet"
                    message="Reviews, guides and features about {{ $category->name }} will be published here."
                    icon="bi-newspaper"
                />
            @else
                <div class="fh-grid">
                    @foreach($articles as $item)
                        <x-article-card :item="$item" />
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Multimedia --}}
        <div class="tab-pane fade" id="pane-multimedia" role="tabpanel" aria-labelledby="tab-multimedia" tabindex="0">
            @if($multimedia->isEmpty())
                <x-empty
                    title="No multimedia yet"
                    message="Trailers, clips and galleries for {{ $category->name }} will be featured here."
                    icon="bi-play-circle"
                />
            @else
                <div class="fh-grid">
                    @foreach($multimedia as $item)
                        <x-media-card :item="$item" />
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Releases --}}
        <div class="tab-pane fade" id="pane-releases" role="tabpanel" aria-labelledby="tab-releases" tabindex="0">
            @if($releases->isEmpty())
                <x-empty
                    title="No releases on the calendar"
                    message="Upcoming {{ $category->name }} drop dates will be tracked here."
                    icon="bi-calendar-event"
                />
            @else
                <div class="fh-grid">
                    @foreach($releases as $item)
                        <x-release-card :item="$item" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var form = document.getElementById('cat-filters');
            if (!form) return;
            form.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', function () { form.submit(); });
            });
        })();
    </script>
@endsection
