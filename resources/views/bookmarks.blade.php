@extends('layouts.app')

@section('title', 'My bookmarks · Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Dashboard', 'to' => route('dashboard')],
        ['label' => 'Bookmarks'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Saved for later</span>
            <h1 class="fh-page-title">My bookmarks</h1>
            <p class="fh-page-sub">
                {{ $total === 1 ? 'One item saved' : number_format($total).' items saved' }} —
                filter by type, search your notes, keep what matters.
            </p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('explore') }}">
            <i class="bi bi-compass" aria-hidden="true"></i> Find more to save
        </a>
    </header>

    {{-- Filters --}}
    <div class="fh-toolbar">
        <div class="d-flex flex-wrap gap-2" role="group" aria-label="Filter bookmarks by type">
            <a
                class="fh-chip {{ $activeType === 'all' ? 'is-active' : '' }}"
                href="{{ route('bookmarks.index', array_filter(['type' => 'all', 'q' => $search, 'sort' => $sort], fn ($value) => $value !== '' && $value !== null)) }}"
                @if ($activeType === 'all') aria-current="true" @endif
            >
                <i class="bi bi-bookmark-star" aria-hidden="true"></i>
                All <span class="fh-badge fh-badge-slate rounded-pill ms-1">{{ $counts['all'] ?? 0 }}</span>
            </a>

            @foreach ($types as $slug => $class)
                @php $typeCount = $counts[$slug] ?? 0; @endphp
                @if ($typeCount > 0 || $activeType === $slug)
                    <a
                        class="fh-chip {{ $activeType === $slug ? 'is-active' : '' }}"
                        href="{{ route('bookmarks.index', array_filter(['type' => $slug, 'q' => $search, 'sort' => $sort], fn ($value) => $value !== '' && $value !== null)) }}"
                        @if ($activeType === $slug) aria-current="true" @endif
                    >
                        {{ \App\Support\BookmarkHelper::typeLabel($slug) }}
                        <span class="fh-badge fh-badge-slate rounded-pill ms-1">{{ $typeCount }}</span>
                    </a>
                @endif
            @endforeach
        </div>

        <form class="fh-search" role="search" method="GET" action="{{ route('bookmarks.index') }}">
            <label class="visually-hidden" for="bookmark-search">Search bookmarks</label>
            @if ($activeType !== 'all')
                <input type="hidden" name="type" value="{{ $activeType }}">
            @endif
            <input type="hidden" name="sort" value="{{ $sort }}">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                id="bookmark-search"
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search titles and notes…"
                autocomplete="off"
            >
            <button class="btn btn-fh btn-sm" type="submit">
                <i class="bi bi-search" aria-hidden="true"></i>
                <span class="visually-hidden">Search bookmarks</span>
            </button>
        </form>

        <form method="GET" action="{{ route('bookmarks.index') }}">
            @if ($activeType !== 'all')
                <input type="hidden" name="type" value="{{ $activeType }}">
            @endif
            @if ($search !== '')
                <input type="hidden" name="q" value="{{ $search }}">
            @endif
            <label class="visually-hidden" for="bookmark-sort">Sort bookmarks</label>
            <select id="bookmark-sort" class="fh-field" name="sort" style="width:auto;" onchange="this.form.submit()">
                <option value="recent" @selected($sort === 'recent')>Newest first</option>
                <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
            </select>
            <noscript><button class="btn btn-fh-ghost btn-sm ms-2" type="submit">Sort</button></noscript>
        </form>
    </div>

    {{-- Results --}}
    @if ($bookmarks->isEmpty())
        @if ($search !== '' || $activeType !== 'all')
            <x-empty
                title="No bookmarks match those filters"
                message="Try a different search term or switch back to all types."
                icon="bi-search"
            >
                <a
                    class="btn btn-fh-ghost btn-sm"
                    href="{{ route('bookmarks.index') }}"
                >
                    Clear filters
                </a>
            </x-empty>
        @else
            <x-empty
                title="No bookmarks yet"
                message="Tap the bookmark icon on any card, character or event and it will wait for you here."
                icon="bi-bookmark"
            >
                <a class="btn btn-fh btn-sm" href="{{ route('explore') }}">Start exploring</a>
            </x-empty>
        @endif
    @else
        <div class="fh-grid-3 fh-grid mb-4">
            @foreach ($bookmarks as $row)
                <article class="fh-card h-100">
                    <div class="fh-card-media fh-media fh-media-16x9">
                        @if ($row['url'])
                            <a href="{{ $row['url'] }}" aria-label="Open {{ $row['title'] }}">
                                <img
                                    class="fh-img"
                                    src="{{ img($row['image'], $row['title'], 640, 360, $row['type_slug']) }}"
                                    alt="{{ $row['title'] }}"
                                    width="640"
                                    height="360"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </a>
                        @else
                            <img
                                class="fh-img"
                                src="{{ ph($row['title'], 640, 360, 'slate') }}"
                                alt="{{ $row['title'] }}"
                                width="640"
                                height="360"
                                loading="lazy"
                                decoding="async"
                            >
                        @endif
                    </div>

                    <div class="fh-card-body">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <x-badge :variant="$row['missing'] ? 'slate' : 'violet'">
                                {{ $row['type_label'] }}
                            </x-badge>

                            <div class="d-flex gap-1">
                                <button
                                    type="button"
                                    class="fh-bookmark-note{{ filled($row['note']) ? ' has-note' : '' }}"
                                    data-bookmark-note
                                    data-update="{{ route('bookmarks.update', $row['id']) }}"
                                    data-note="{{ (string) ($row['note'] ?? '') }}"
                                    aria-label="Edit your note for {{ $row['title'] }}"
                                    title="Edit note"
                                >
                                    <i class="bi {{ filled($row['note']) ? 'bi-pencil-fill' : 'bi-pencil-square' }}" aria-hidden="true"></i>
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('bookmarks.destroy', $row['id']) }}"
                                    data-confirm="Remove “{{ $row['title'] }}” from your bookmarks?"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="fh-bookmark-note"
                                        aria-label="Remove {{ $row['title'] }} from bookmarks"
                                        title="Remove bookmark"
                                    >
                                        <i class="bi bi-trash3" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <h3 class="fh-card-title fh-clamp-2">
                            @if ($row['url'])
                                <a href="{{ $row['url'] }}">{{ $row['title'] }}</a>
                            @else
                                {{ $row['title'] }}
                            @endif
                        </h3>

                        <p class="fh-text-sm fh-text-dim fh-clamp-2 mb-2">
                            {{ $row['subtitle'] !== '' ? $row['subtitle'] : ($row['missing'] ? 'The source entry has been deleted.' : 'Saved item') }}
                        </p>

                        @if (filled($row['note']))
                            <p class="fh-help mb-2">
                                <i class="bi bi-quote" aria-hidden="true"></i>
                                {{ \Illuminate\Support\Str::limit((string) $row['note'], 90) }}
                            </p>
                        @endif

                        <div class="fh-card-foot">
                            <span class="fh-meta-item fh-text-dim fh-text-sm">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                {{ $row['created_at']?->format('M j, Y') }}
                            </span>
                            @if ($row['url'])
                                <a class="fh-card-cta" href="{{ $row['url'] }}">
                                    Open <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                </a>
                            @else
                                <span class="fh-text-sm fh-text-dim">Unavailable</span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <x-pagination :paginator="$bookmarks" />
    @endif
@endsection
