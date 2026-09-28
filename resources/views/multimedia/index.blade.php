@extends('layouts.app')

@section('title', 'Multimedia · Fan Hub+')

@section('styles')
<style>
    .fh-sort-form {
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .fh-tab-count {
        margin-left: .35rem;
    }
</style>
@endsection

@section('content')
<x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Multimedia']]" />

<x-page-banner
    kicker="Watch, listen, browse"
    title="Multimedia Center"
    subtitle="Videos, trailers, galleries and audio — every clip, soundtrack and photo set in one library."
    image="{{ asset('images/catalogue/banner-multimedia.png') }}"
/>

@if($error !== null)
    <x-error :message="$error">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('media.index') }}">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Try again
        </a>
    </x-error>
@else
    @php
        $tabLabels = [
            'video' => ['label' => 'VIDEOS', 'icon' => 'bi-play-circle-fill'],
            'trailer' => ['label' => 'TRAILERS', 'icon' => 'bi-film'],
            'audio' => ['label' => 'AUDIO', 'icon' => 'bi-music-note-beamed'],
            'gallery' => ['label' => 'GALLERIES', 'icon' => 'bi-images'],
        ];

        $emptyStates = [
            'video' => ['title' => 'No videos yet', 'message' => 'Interviews, fan edits and behind-the-scenes clips will appear here as soon as they are uploaded.'],
            'trailer' => ['title' => 'No trailers yet', 'message' => 'Official trailers land here the moment they drop — check back after the next reveal.'],
            'audio' => ['title' => 'No audio yet', 'message' => 'Soundtracks, voice reels and convention panels will show up in this tab.'],
            'gallery' => ['title' => 'No galleries yet', 'message' => 'Photo sets, concept art and cosplay shoots will be collected here.'],
        ];

        $activeTab = $tabLabels[$type];
        $emptyState = $emptyStates[$type];
        $tabQuery = array_filter(['q' => $search, 'sort' => $sort], fn ($value) => $value !== '');
    @endphp

    <ul class="nav nav-pills mb-3" role="tablist" aria-label="Multimedia types">
        @foreach($tabLabels as $tabKey => $tab)
            <li class="nav-item" role="presentation">
                <a
                    class="nav-link {{ $type === $tabKey ? 'active' : '' }}"
                    href="{{ route('media.index', array_merge($tabQuery, ['type' => $tabKey])) }}"
                    @if($type === $tabKey) aria-current="page" @endif
                    role="tab"
                >
                    <i class="bi {{ $tab['icon'] }}" aria-hidden="true"></i>
                    {{ $tab['label'] }}
                    <span class="fh-tab-count fh-badge fh-badge-slate rounded-pill">{{ number_format((int) ($counts[$tabKey] ?? 0)) }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="fh-toolbar">
        <form class="fh-search" role="search" action="{{ route('media.index') }}" method="get">
            <label class="visually-hidden" for="media-search">Search multimedia</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                type="search"
                id="media-search"
                name="q"
                value="{{ $search }}"
                placeholder="Search {{ strtolower($activeTab['label']) }}…"
                autocomplete="off"
            >
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <button class="btn btn-fh btn-sm" type="submit">
                <span class="d-none d-sm-inline">Search</span>
                <i class="bi bi-search d-sm-none" aria-hidden="true"></i>
            </button>
        </form>

        <form class="fh-sort-form" action="{{ route('media.index') }}" method="get">
            @if($search !== '')
                <input type="hidden" name="q" value="{{ $search }}">
            @endif
            <input type="hidden" name="type" value="{{ $type }}">
            <label class="fh-label mb-0" for="media-sort">Sort</label>
            <select class="fh-field" id="media-sort" name="sort" style="width:auto" onchange="this.form.submit()">
                <option value="latest" @selected($sort === 'latest')>Newest first</option>
                <option value="popular" @selected($sort === 'popular')>Most viewed</option>
                <option value="rating" @selected($sort === 'rating')>Top rated</option>
            </select>
            <button class="btn btn-fh-outline btn-sm d-none d-lg-inline-flex" type="submit">Apply</button>
        </form>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <p class="fh-eyebrow mb-0">{{ $activeTab['label'] }} · {{ number_format($total) }} {{ $total === 1 ? 'entry' : 'entries' }}</p>

        @if($activeCategory)
            <a class="fh-chip fh-chip-slate" href="{{ route('media.index', $tabQuery) }}">
                <i class="bi bi-x-circle" aria-hidden="true"></i> {{ $activeCategory->name }} filter on — clear
            </a>
        @endif
    </div>

    @if($media->isEmpty())
        @if($search !== '' || $activeCategory)
            <x-empty
                title="Nothing matches that search"
                message="Try a different keyword, or jump back to the full {{ strtolower($activeTab['label']) }} tab."
                icon="bi-search"
            >
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('media.index', ['type' => $type]) }}">
                    <i class="bi bi-x-circle" aria-hidden="true"></i> Clear search
                </a>
            </x-empty>
        @else
            <x-empty
                :title="$emptyState['title']"
                :message="$emptyState['message']"
                icon="bi-play-circle"
            >
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('media.index', ['type' => 'video']) }}">
                    <i class="bi bi-play-fill" aria-hidden="true"></i> Browse videos
                </a>
            </x-empty>
        @endif
    @else
        <div class="fh-grid fh-grid-3">
            @foreach($media as $mediaItem)
                <x-media-card :item="$mediaItem" />
            @endforeach
        </div>

        <x-pagination :paginator="$media" />
    @endif
@endif
@endsection
