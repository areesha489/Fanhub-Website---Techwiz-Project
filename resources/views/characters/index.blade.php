@extends('layouts.app')

@section('title', 'Characters · Fan Hub+')

@section('styles')
<style>
    .fh-chip.is-active {
        background: rgba(139, 92, 246, .22);
        border-color: rgba(139, 92, 246, .55);
        color: #c4b5fd;
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
</style>
@endsection

@section('content')
<x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Characters']]" />

<x-page-banner
    kicker="Character vault"
    title="Characters"
    subtitle="Meet your favourite characters — browse heroes, rivals and sidekicks across every fandom, then open a profile for the full story."
    image="{{ asset('images/catalogue/banner-characters.png') }}"
>
    @if($error === null)
        <span class="fh-badge fh-badge-violet">{{ number_format($total) }} {{ $total === 1 ? 'profile' : 'profiles' }}</span>
    @endif
</x-page-banner>

@if($error !== null)
    <x-error :message="$error">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('characters.index') }}">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Try again
        </a>
    </x-error>
@else
    @php
        $activeFilters = array_filter([
            'q' => $search,
            'category' => $category,
            'sort' => $sort !== 'popular' ? $sort : '',
        ], fn ($value) => $value !== '');
    @endphp

    <div class="fh-toolbar">
        <form class="fh-search" role="search" action="{{ route('characters.index') }}" method="get">
            <label class="visually-hidden" for="characters-search">Search characters</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                type="search"
                id="characters-search"
                name="q"
                value="{{ $search }}"
                placeholder="Search by name, fandom or biography…"
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

        <form class="fh-sort-form" action="{{ route('characters.index') }}" method="get">
            @if($search !== '')
                <input type="hidden" name="q" value="{{ $search }}">
            @endif
            @if($category !== '')
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <label class="fh-label mb-0" for="characters-sort">Sort</label>
            <select class="fh-field" id="characters-sort" name="sort" style="width:auto" onchange="this.form.submit()">
                <option value="popular" @selected($sort === 'popular')>Most popular</option>
                <option value="latest" @selected($sort === 'latest')>Recently added</option>
                <option value="alphabetical" @selected($sort === 'alphabetical')>A → Z</option>
            </select>
            <button class="btn btn-fh-outline btn-sm d-none d-lg-inline-flex" type="submit">Apply</button>
        </form>
    </div>

    <nav class="fh-chip-row" aria-label="Filter characters by category">
        <a
            class="fh-chip {{ $category === '' ? 'is-active' : '' }}"
            href="{{ route('characters.index', array_filter(['q' => $search, 'sort' => $sort], fn ($value) => $value !== '')) }}"
            @if($category === '') aria-current="true" @endif
        >
            <i class="bi bi-collection-fill" aria-hidden="true"></i> All fandoms
        </a>

        @foreach($categories as $categoryOption)
            <a
                class="fh-chip {{ $category === $categoryOption->slug ? 'is-active' : '' }}"
                href="{{ route('characters.index', array_filter(['q' => $search, 'category' => $categoryOption->slug, 'sort' => $sort], fn ($value) => $value !== '')) }}"
                @if($category === $categoryOption->slug) aria-current="true" @endif
            >
                <i class="bi {{ $categoryOption->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                {{ $categoryOption->name }}
            </a>
        @endforeach
    </nav>

    @if($characters->isEmpty())
        @if($activeFilters !== [])
            <x-empty
                title="No characters match those filters"
                message="Try a different search term or clear the filters to see the whole roster."
                icon="bi-search"
            >
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('characters.index') }}">
                    <i class="bi bi-x-circle" aria-hidden="true"></i> Clear filters
                </a>
            </x-empty>
        @else
            <x-empty
                title="No characters to showcase yet"
                message="New profiles land here as soon as they are added to the vault."
                icon="bi-people"
            >
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('explore') }}">
                    <i class="bi bi-compass" aria-hidden="true"></i> Browse Explore
                </a>
            </x-empty>
        @endif
    @else
        <div class="fh-grid fh-character-grid">
            @foreach($characters as $character)
                <x-character-card :item="$character" />
            @endforeach
        </div>

        <x-pagination :paginator="$characters" />
    @endif
@endif
@endsection
