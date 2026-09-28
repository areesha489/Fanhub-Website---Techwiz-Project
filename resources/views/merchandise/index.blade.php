@extends('layouts.app')

@section('title', 'Merchandise · Fan Hub+')

@section('content')
<x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Merchandise']]" />

<x-page-banner
    kicker="Collector's corner"
    title="Merchandise Showcase"
    subtitle="Official and fan-favourite collectibles — figures, artbooks, apparel and limited drops worth hunting down."
    image="{{ asset('images/catalogue/banner-merch.png') }}"
/>

<div class="fh-card fh-card-pad mb-4 d-flex flex-wrap align-items-center gap-2" style="background: rgba(251, 191, 36, .08);">
    <i class="bi bi-shield-check" aria-hidden="true" style="font-size: 1.15rem; color: var(--fh-amber);"></i>
    <p class="mb-0 fh-text-sm">
        <strong>Display only — Fan Hub+ does not sell or process payments.</strong>
        Prices are shown for reference; purchases happen with the official retailers.
    </p>
</div>

@if($error !== null)
    <x-error :message="$error">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('merchandise.index') }}">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Try again
        </a>
    </x-error>
@else
    @php
        $hasFilters = $fandom !== '' || $category !== '' || $tag !== '' || $upcoming !== '' || $sort !== 'popular';
    @endphp

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <p class="fh-eyebrow mb-0">{{ number_format($total) }} {{ $total === 1 ? 'collectible' : 'collectibles' }} on display</p>

        @if($hasFilters)
            <a class="fh-chip fh-chip-slate" href="{{ route('merchandise.index') }}">
                <i class="bi bi-x-circle" aria-hidden="true"></i> Clear filters
            </a>
        @endif
    </div>

    <div class="fh-layout-with-aside">
        <x-filter-panel :action="route('merchandise.index')" title="Filter showcase">
            <div class="fh-filter-group">
                <label class="fh-label" for="merch-fandom">Fandom</label>
                <select class="fh-field" id="merch-fandom" name="fandom" onchange="this.form.submit()">
                    <option value="">Every fandom</option>
                    @foreach($fandoms as $fandomOption)
                        <option value="{{ $fandomOption }}" @selected($fandom === $fandomOption)>{{ $fandomOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="merch-category">Category</label>
                <select class="fh-field" id="merch-category" name="category" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach($categories as $categoryOption)
                        <option value="{{ $categoryOption->slug }}" @selected($category === $categoryOption->slug)>{{ $categoryOption->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="merch-tag">Tag</label>
                <select class="fh-field" id="merch-tag" name="tag" onchange="this.form.submit()">
                    <option value="">Any tag</option>
                    @foreach($tags as $tagOption)
                        <option value="{{ $tagOption }}" @selected($tag === $tagOption)>{{ $tagOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="merch-upcoming">Availability</label>
                <select class="fh-field" id="merch-upcoming" name="upcoming" onchange="this.form.submit()">
                    <option value="">Everything</option>
                    <option value="0" @selected($upcoming === '0')>In stock / released</option>
                    <option value="1" @selected($upcoming === '1')>Upcoming pre-orders</option>
                </select>
            </div>

            <div class="fh-filter-group">
                <label class="fh-label" for="merch-sort">Sort by</label>
                <select class="fh-field" id="merch-sort" name="sort" onchange="this.form.submit()">
                    <option value="popular" @selected($sort === 'popular')>Most popular</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>Price: low to high</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>Price: high to low</option>
                    <option value="latest" @selected($sort === 'latest')>Newest first</option>
                </select>
            </div>

            <button class="btn btn-fh w-100" type="submit">
                <i class="bi bi-funnel" aria-hidden="true"></i> Apply filters
            </button>
        </x-filter-panel>

        <div class="min-w-0">
            @if($items->isEmpty())
                @if($hasFilters)
                    <x-empty
                        title="No collectibles match those filters"
                        message="Widen the selection — try another fandom, tag or sort order to see the full shelf."
                        icon="bi-handbag"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('merchandise.index') }}">
                            <i class="bi bi-x-circle" aria-hidden="true"></i> Clear filters
                        </a>
                    </x-empty>
                @else
                    <x-empty
                        title="The shelves are empty for now"
                        message="Curated collectibles will be showcased here as soon as the catalogue is stocked."
                        icon="bi-handbag"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('explore') }}">
                            <i class="bi bi-compass" aria-hidden="true"></i> Explore the catalogue
                        </a>
                    </x-empty>
                @endif
            @else
                <div class="fh-grid fh-grid-3">
                    @foreach($items as $item)
                        <x-merch-card :item="$item" />
                    @endforeach
                </div>

                <x-pagination :paginator="$items" />
            @endif
        </div>
    </div>
@endif
@endsection
