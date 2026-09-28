@extends('layouts.app')

@section('title', $media?->title ? $media->title.' · Multimedia · Fan Hub+' : 'Multimedia · Fan Hub+')

@section('styles')
<style>
    .fh-player {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        background: #03060f;
        border-radius: var(--fh-radius-lg);
        overflow: hidden;
        border: 1px solid var(--fh-border);
    }

    .fh-player iframe,
    .fh-player video {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
        background: #000;
    }

    .fh-player-poster {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fh-player-poster img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: .55;
    }

    .fh-player-poster .fh-play-link {
        position: relative;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4.5rem;
        height: 4.5rem;
        border-radius: 50%;
        background: var(--fh-grad-primary);
        color: #fff;
        font-size: 1.7rem;
        box-shadow: 0 12px 34px rgba(139, 92, 246, .45);
        transition: transform var(--fh-t);
    }

    .fh-player-poster .fh-play-link:hover {
        transform: scale(1.08);
    }

    .fh-player-note {
        position: relative;
        z-index: 2;
        text-align: center;
        padding: 1.5rem;
    }

    .fh-audio-card {
        background: linear-gradient(160deg, var(--fh-card-2), var(--fh-card));
        border: 1px solid var(--fh-border);
        border-radius: var(--fh-radius-lg);
        padding: clamp(1.25rem, 3vw, 2rem);
    }

    .fh-audio-card audio {
        width: 100%;
    }

    .fh-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 240px), 1fr));
        gap: .85rem;
    }

    .fh-gallery-grid button {
        padding: 0;
        border: 0;
        background: none;
        border-radius: var(--fh-radius);
        overflow: hidden;
        cursor: pointer;
    }

    .fh-media-list {
        display: flex;
        flex-direction: column;
        gap: .8rem;
    }

    .fh-media-list-item {
        display: flex;
        gap: .7rem;
        align-items: center;
        color: inherit;
        text-decoration: none;
    }

    .fh-media-list-item:hover .fh-media-list-title {
        color: var(--fh-violet-2);
    }

    .fh-media-list-thumb {
        width: 84px;
        flex: 0 0 84px;
        aspect-ratio: 16 / 9;
        border-radius: var(--fh-radius-sm);
        overflow: hidden;
    }

    .fh-media-list-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .fh-media-list-title {
        font-weight: 600;
        font-size: .92rem;
        line-height: 1.35;
        color: var(--fh-text);
    }
</style>
@endsection

@section('content')
@if($loadError !== null || $media === null)
    <x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Multimedia', 'to' => route('media.index')]]" />
    <x-error :message="$loadError ?? 'We could not load this media entry right now. Please try again in a moment. The link you followed may point to something that was removed.'">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('media.index') }}">
            <i class="bi bi-collection-play" aria-hidden="true"></i> Back to multimedia
        </a>
    </x-error>
@else
    @php
        $mediaCategory = $media->category;
        $mediaType = in_array($media->media_type, ['video', 'trailer', 'audio', 'gallery'], true) ? $media->media_type : 'video';

        $typeMeta = [
            'video' => ['label' => 'Video', 'icon' => 'bi-play-circle-fill', 'variant' => 'violet'],
            'trailer' => ['label' => 'Trailer', 'icon' => 'bi-film', 'variant' => 'pink'],
            'audio' => ['label' => 'Audio', 'icon' => 'bi-music-note-beamed', 'variant' => 'cyan'],
            'gallery' => ['label' => 'Gallery', 'icon' => 'bi-images', 'variant' => 'amber'],
        ][$mediaType];

        $poster = img($media->thumbnail, $media->title, 1280, 720, $mediaCategory?->slug);
        $outboundUrl = $media->url ?: $media->embed_url;
    @endphp

    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Multimedia', 'to' => route('media.index')],
        ['label' => $media->title],
    ]" />

    <div class="fh-layout-with-aside fh-aside-right">
        <div class="min-w-0">
            <div class="mb-4">
                @if($mediaType === 'audio')
                    <div class="fh-audio-card">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <span class="fh-chip fh-chip-cyan">
                                <i class="bi bi-music-note-beamed" aria-hidden="true"></i> Now playing
                            </span>
                            @if($media->duration)
                                <span class="fh-text-dim fh-text-sm">
                                    <i class="bi bi-hourglass-split" aria-hidden="true"></i> {{ $media->duration }}
                                </span>
                            @endif
                        </div>

                        @if($embedUrl)
                            <div class="fh-player">
                                <iframe
                                    src="{{ $embedUrl }}"
                                    title="Audio player for {{ $media->title }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen
                                    referrerpolicy="strict-origin-when-cross-origin"
                                ></iframe>
                            </div>
                        @elseif($media->url)
                            <audio controls preload="metadata" src="{{ $media->url }}" aria-label="Audio player for {{ $media->title }}">
                                Your browser does not support the audio element.
                            </audio>
                            <p class="fh-text-dim fh-text-sm mt-3 mb-0">
                                <a href="{{ $media->url }}" target="_blank" rel="noopener">Open the audio file in a new tab</a>
                            </p>
                        @else
                            <x-empty
                                title="No audio file attached"
                                message="This entry is catalogued but the audio source has not been published yet."
                                icon="bi-music-note-beamed"
                            />
                        @endif
                    </div>
                @elseif($mediaType === 'gallery')
                    @if($galleryImages->isNotEmpty())
                        <div class="fh-gallery-grid">
                            @foreach($galleryImages as $index => $galleryImage)
                                <button
                                    type="button"
                                    data-lightbox
                                    data-lightbox-src="{{ img(is_string($galleryImage) ? $galleryImage : null, $media->title, 1280, 720, $mediaCategory?->slug) }}"
                                    data-lightbox-alt="{{ $media->title }} image {{ $index + 1 }}"
                                    aria-label="Open {{ $media->title }} image {{ $index + 1 }}"
                                >
                                    <div class="fh-media fh-media-16x9">
                                        <img
                                            class="fh-img"
                                            src="{{ img(is_string($galleryImage) ? $galleryImage : null, $media->title, 800, 450, $mediaCategory?->slug) }}"
                                            alt="{{ $media->title }} image {{ $index + 1 }}"
                                            width="800"
                                            height="450"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <x-empty
                            title="This gallery is empty"
                            message="Images will appear here as soon as the set is uploaded."
                            icon="bi-images"
                        />
                    @endif
                @elseif($fileUrl && in_array($mediaType, ['video', 'trailer'], true))
                    <div class="fh-player">
                        <video controls playsinline preload="metadata" src="{{ $fileUrl }}" aria-label="Video player for {{ $media->title }}">
                            Your browser does not support the video element.
                        </video>
                    </div>
                @elseif($embedUrl)
                    <div class="fh-player">
                        <iframe
                            src="{{ $embedUrl }}"
                            title="Embedded player for {{ $media->title }}"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                        ></iframe>
                    </div>
                @else
                    <div class="fh-player">
                        <div class="fh-player-poster">
                            <img
                                class="fh-img"
                                src="{{ $poster }}"
                                alt="Poster frame for {{ $media->title }}"
                                width="1280"
                                height="720"
                                decoding="async"
                            >

                            @if($outboundUrl)
                                <a
                                    class="fh-play-link"
                                    href="{{ $outboundUrl }}"
                                    target="_blank"
                                    rel="noopener"
                                    aria-label="Play {{ $media->title }} in a new tab"
                                >
                                    <i class="bi bi-play-fill" aria-hidden="true"></i>
                                </a>
                            @else
                                <div class="fh-player-note">
                                    <p class="fh-text-muted-2 mb-1">No playable source published yet</p>
                                    <p class="fh-text-dim fh-text-sm mb-0">The editors are still encoding this clip — check back shortly.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <article class="fh-card fh-card-pad mb-4">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <x-badge variant="{{ $typeMeta['variant'] }}">
                        <i class="bi {{ $typeMeta['icon'] }}" aria-hidden="true"></i> {{ $typeMeta['label'] }}
                    </x-badge>

                    @if($mediaCategory)
                        <a class="fh-chip" href="{{ route('category.show', $mediaCategory->slug) }}">
                            <i class="bi {{ $mediaCategory->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                            {{ $mediaCategory->name }}
                        </a>
                    @endif

                    @if($media->duration)
                        <x-badge variant="slate">{{ $media->duration }}</x-badge>
                    @endif

                    @if($media->featured)
                        <x-badge variant="amber">Featured</x-badge>
                    @endif
                </div>

                <h1 class="fh-title mb-2">{{ $media->title }}</h1>

                @if($media->description)
                    <p class="fh-text-muted-2 mb-3">{{ $media->description }}</p>
                @else
                    <p class="fh-text-muted-2 mb-3">A {{ strtolower($typeMeta['label']) }} entry from the Fan Hub+ library — open it, rate it and share it with the fandom.</p>
                @endif

                <div class="fh-card-meta mb-3">
                    <span class="fh-meta-item">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                        {{ number_format((int) $media->views_count) }} views
                    </span>
                    <span class="fh-meta-item">
                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                        {{ $media->created_at->format('M j, Y') }}
                    </span>
                    <span class="fh-meta-item">
                        <x-rating :value="(float) $media->rating" size="sm" />
                    </span>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div>
                            <p class="fh-label mb-1">Rate this entry</p>
                            <div
                                class="fh-rate-input"
                                data-rate
                                data-type="media"
                                data-id="{{ $media->id }}"
                                data-value="{{ (int) round((float) $media->rating) }}"
                                role="group"
                                aria-label="Rate {{ $media->title }} from 1 to 10"
                            >
                                @for($star = 1; $star <= 10; $star++)
                                    <button type="button" class="fh-rate-star" data-value="{{ $star }}" aria-label="{{ $star }} out of 10">★</button>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <x-bookmark
                            type="media"
                            :id="$media->id"
                            :bookmarked="$isBookmarked"
                            :bookmark-id="$bookmarkId"
                        />
                        <button
                            type="button"
                            class="btn btn-fh-outline btn-sm"
                            data-share-copy
                            aria-label="Share this media entry"
                        >
                            <i class="bi bi-share" aria-hidden="true"></i> Share
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <aside class="d-flex flex-column gap-3" aria-label="Media sidebar">
            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-collection-play" aria-hidden="true"></i> More Media
                </p>

                @if($related->isEmpty())
                    <p class="fh-text-dim fh-text-sm mb-0">Nothing else is published in this corner of the library yet.</p>
                @else
                    <div class="fh-media-list">
                        @foreach($related as $relatedItem)
                            <a class="fh-media-list-item" href="{{ route('media.show', $relatedItem->slug) }}">
                                <span class="fh-media-list-thumb">
                                    <img
                                        class="fh-img"
                                        src="{{ img($relatedItem->thumbnail, $relatedItem->title, 320, 180, $relatedItem->category?->slug) }}"
                                        alt="{{ $relatedItem->title }}"
                                        width="320"
                                        height="180"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </span>
                                <span class="min-w-0">
                                    <span class="fh-media-list-title fh-clamp-2 d-block">{{ $relatedItem->title }}</span>
                                    <span class="fh-text-dim fh-text-sm">
                                        {{ ucfirst($relatedItem->media_type) }}
                                        @if($relatedItem->category) · {{ $relatedItem->category->name }} @endif
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-info-circle" aria-hidden="true"></i> Entry details
                </p>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 fh-text-sm">
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Type</span>
                        <span class="fh-fw-600">{{ $typeMeta['label'] }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Category</span>
                        <span class="fh-fw-600">{{ $mediaCategory?->name ?: 'Uncategorised' }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Duration</span>
                        <span class="fh-fw-600">{{ $media->duration ?: '—' }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-2">
                        <span class="fh-text-dim">Added</span>
                        <span class="fh-fw-600">{{ $media->created_at->format('M j, Y') }}</span>
                    </li>
                </ul>
            </div>
        </aside>
    </div>

    <div class="modal fade" id="media-gallery-modal" tabindex="-1" aria-labelledby="media-gallery-label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content fh-glass">
                <div class="modal-header">
                    <h2 class="modal-title fh-text-sm" id="media-gallery-label">{{ $media->title }} — gallery</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close image"></button>
                </div>
                <div class="modal-body text-center p-2">
                    <img id="media-gallery-image" class="fh-img w-100" src="{{ $galleryImages->first() ? img(is_string($galleryImages->first()) ? $galleryImages->first() : null, $media->title, 1280, 720, $mediaCategory?->slug) : $poster }}" alt="" width="1280" height="720" decoding="async">
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    (function () {
        var modalElement = document.getElementById('media-gallery-modal');
        var modalImage = document.getElementById('media-gallery-image');
        var modalLabel = document.getElementById('media-gallery-label');

        document.querySelectorAll('[data-lightbox]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!modalElement || !modalImage) return;
                modalImage.src = button.getAttribute('data-lightbox-src') || '';
                modalImage.alt = button.getAttribute('data-lightbox-alt') || '';
                if (modalLabel) modalLabel.textContent = modalImage.alt;
                var modal = window.bootstrap && window.bootstrap.Modal.getOrCreateInstance(modalElement);
                if (modal) modal.show();
            });
        });

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
    })();
</script>
@endsection
