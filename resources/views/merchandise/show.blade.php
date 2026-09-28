@extends('layouts.app')

@section('title', $item?->title ? $item->title.' · Merchandise · Fan Hub+' : 'Merchandise · Fan Hub+')

@section('content')
@if($loadError !== null || $item === null)
    <x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Merchandise', 'to' => route('merchandise.index')]]" />
    <x-error :message="$loadError ?? 'We could not load this collectible right now. Please try again in a moment. The link you followed may point to an item that was removed.'">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('merchandise.index') }}">
            <i class="bi bi-handbag" aria-hidden="true"></i> Back to the showcase
        </a>
    </x-error>
@else
    @php
        $itemCategory = $item->category;
        $itemTags = collect($item->tags ?: []);
        $itemImage = img($item->image, $item->title, 900, 900, $itemCategory?->slug);

        $releaseLabel = null;
        if ($item->release_date) {
            $releaseLabel = $item->release_date->format('M j, Y');
        }
    @endphp

    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Merchandise', 'to' => route('merchandise.index')],
        ['label' => $item->title],
    ]" />

    <div class="fh-card fh-card-pad mb-3" style="background: rgba(251, 191, 36, .08);">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <i class="bi bi-shield-check" aria-hidden="true" style="color: var(--fh-amber);"></i>
            <p class="mb-0 fh-text-sm"><strong>Display only — Fan Hub+ does not sell or process payments.</strong></p>
        </div>
    </div>

    <div class="fh-layout-with-aside fh-aside-right">
        <div class="min-w-0">
            <article class="fh-card fh-card-pad mb-4">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-sm-5 col-md-4 col-lg-4">
                        <div class="fh-media fh-media-1x1" style="border-radius: var(--fh-radius-lg); overflow: hidden;">
                            <img
                                class="fh-img"
                                src="{{ $itemImage }}"
                                alt="{{ $item->title }}"
                                width="900"
                                height="900"
                                decoding="async"
                            >
                        </div>
                    </div>

                    <div class="col-12 col-sm-7 col-md-8 col-lg-8">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            @if($itemCategory)
                                <a class="fh-chip fh-chip-amber" href="{{ route('category.show', $itemCategory->slug) }}">
                                    <i class="bi {{ $itemCategory->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                                    {{ $itemCategory->name }}
                                </a>
                            @endif

                            @if($item->is_upcoming)
                                <x-badge variant="amber">Pre-order</x-badge>
                            @else
                                <x-badge variant="green">Available</x-badge>
                            @endif
                        </div>

                        <h1 class="fh-title mb-1">{{ $item->title }}</h1>

                        @if($item->fandom)
                            <p class="fh-sub mb-3">{{ $item->fandom }}</p>
                        @endif

                        <p class="fh-price mb-3" style="font-size: 1.4rem; font-family: var(--fh-font-display); font-weight: 800;">
                            @if($item->price !== null)
                                {{ $item->currency }} {{ number_format((float) $item->price, 2) }}
                                <span class="fh-text-dim fh-text-sm fw-normal">reference price</span>
                            @else
                                Price TBA
                            @endif
                        </p>

                        @if($itemTags->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($itemTags as $itemTag)
                                    <a class="fh-chip" href="{{ route('merchandise.index', ['tag' => $itemTag]) }}">
                                        <i class="bi bi-tag" aria-hidden="true"></i> {{ $itemTag }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <x-bookmark
                                type="merchandise"
                                :id="$item->id"
                                :bookmarked="$isBookmarked"
                                :bookmark-id="$bookmarkId"
                            />
                            <button
                                type="button"
                                class="btn btn-fh-outline btn-sm"
                                data-share-copy
                                aria-label="Share this collectible"
                            >
                                <i class="bi bi-share" aria-hidden="true"></i> Share
                            </button>
                        </div>
                    </div>
                </div>
            </article>

            @if($item->description)
                <section class="fh-section mb-4">
                    <div class="fh-section-head">
                        <div class="fh-section-head-main">
                            <span class="fh-section-bar" aria-hidden="true"></span>
                            <div class="min-w-0">
                                <h2 class="fh-section-title">About this piece</h2>
                                <p class="fh-section-sub">Why collectors keep it on the shelf</p>
                            </div>
                        </div>
                    </div>

                    <div class="fh-card fh-card-pad" style="max-width: 78ch;">
                        @foreach(collect(preg_split('/\R+/', (string) $item->description))->map(fn (string $paragraph): string => trim($paragraph))->filter() as $paragraph)
                            <p class="fh-text-muted-2">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($related->isNotEmpty())
                <x-section
                    title="More to collect"
                    subtitle="Similar pieces from the same fandom"
                    :href="route('merchandise.index', $item->fandom ? ['fandom' => $item->fandom] : [])"
                    linkText="View all"
                >
                    <div class="fh-grid fh-grid-3">
                        @foreach($related as $relatedItem)
                            <x-merch-card :item="$relatedItem" />
                        @endforeach
                    </div>
                </x-section>
            @endif
        </div>

        <aside class="d-flex flex-column gap-3" aria-label="Collectible sidebar">
            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-info-circle" aria-hidden="true"></i> Item details
                </p>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 fh-text-sm">
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Fandom</span>
                        <span class="fh-fw-600">{{ $item->fandom ?: '—' }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Category</span>
                        <span class="fh-fw-600">{{ $itemCategory?->name ?: 'Uncategorised' }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Availability</span>
                        <span class="fh-fw-600">{{ $item->is_upcoming ? 'Pre-order' : 'Released' }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Release</span>
                        <span class="fh-fw-600">{{ $releaseLabel ?: 'TBA' }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Fans watching</span>
                        <span class="fh-fw-600">{{ number_format((int) $item->popularity) }}</span>
                    </li>
                </ul>
            </div>

            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-cart-dash" aria-hidden="true"></i> How to buy
                </p>
                <p class="fh-text-muted-2 fh-text-sm mb-0">
                    Fan Hub+ is a showcase, not a shop. Use the details here to find the piece with the official
                    retailer or at your local store — we never take payment details.
                </p>
            </div>
        </aside>
    </div>
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
