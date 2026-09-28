@extends('layouts.app')

@section('title', $content->title . ' — Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => $content->category?->name ?? 'Categories', 'to' => $content->category ? route('category.show', $content->category->slug) : route('home')],
        ['label' => $content->title],
    ]" />

    @php
        $categoryName = $content->category?->name;
        $heroImage = img($content->banner_image ?: $content->cover_image, $content->title, 1600, 640, $content->category?->slug);
        $accent = $content->category && preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $content->category->accent) === 1
            ? $content->category->accent
            : '#8B5CF6';
        $statusVariant = match ($content->status) {
            'published' => 'green',
            'archived' => 'slate',
            default => 'amber',
        };
    @endphp

    {{-- Cinematic hero --}}
    <header
        class="fh-card mb-4"
        style="
            position: relative;
            overflow: hidden;
            background-image: linear-gradient(100deg, rgba(5, 8, 22, 0.94) 0%, rgba(5, 8, 22, 0.78) 46%, rgba(5, 8, 22, 0.28) 100%), url('{{ $heroImage }}');
            min-height: 280px;
            background-position: center right;
            background-size: cover;
            background-position: center;
            border-top: 3px solid {{ $accent }};
        "
    >
        <div style="padding: clamp(1.4rem, 4vw, 2.6rem);">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                @if($content->category)
                    <a class="fh-chip" href="{{ route('category.show', $content->category->slug) }}">
                        <i class="bi {{ $content->category->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                        {{ $content->category->name }}
                    </a>
                @endif
                @if($content->content_type)
                    <span class="fh-chip fh-chip-cyan">{{ $content->content_type }}</span>
                @endif
                @if($content->genre)
                    <span class="fh-chip fh-chip-magenta"><i class="bi bi-tag" aria-hidden="true"></i> {{ $content->genre }}</span>
                @endif
                <x-badge variant="{{ $statusVariant }}">{{ ucfirst($content->status) }}</x-badge>
            </div>

            <h1 class="fh-page-title mb-3" style="color: #fff;">{{ $content->title }}</h1>

            <div class="d-flex align-items-center gap-3 flex-wrap mb-4">
                <x-rating :value="$content->rating" />
                @if($content->release_date)
                    <span class="fh-meta-item">
                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                        {{ $content->release_date->format('M j, Y') }}
                    </span>
                @endif
                <span class="fh-meta-item">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                    {{ number_format((int) $content->views_count) }} views
                </span>
                <span class="fh-meta-item">
                    <i class="bi bi-fire" aria-hidden="true"></i>
                    {{ number_format((int) $content->popularity) }} popularity
                </span>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <x-bookmark
                    type="content"
                    :id="$content->id"
                    :bookmarked="$bookmarkId !== null"
                    :bookmark-id="$bookmarkId"
                    size="lg"
                />
                <button class="btn btn-fh-outline" type="button" data-share aria-label="Copy a link to this page">
                    <i class="bi bi-share" aria-hidden="true"></i> Share
                </button>
                @if($trailerEmbed)
                    <button class="btn btn-fh" type="button" id="watch-trailer">
                        <i class="bi bi-play-fill" aria-hidden="true"></i> Watch trailer
                    </button>
                @endif
            </div>
        </div>
    </header>

    @if($error)
        <x-error :message="$error" class="mb-4" />
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            {{-- Body tabs --}}
            <ul class="nav nav-tabs mb-4" role="tablist" aria-label="{{ $content->title }} sections">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-overview" data-bs-toggle="tab" data-bs-target="#pane-overview" type="button" role="tab" aria-controls="pane-overview" aria-selected="true">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-characters" data-bs-toggle="tab" data-bs-target="#pane-characters" type="button" role="tab" aria-controls="pane-characters" aria-selected="false">Characters</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-media" data-bs-toggle="tab" data-bs-target="#pane-media" type="button" role="tab" aria-controls="pane-media" aria-selected="false">Media</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-related" data-bs-toggle="tab" data-bs-target="#pane-related" type="button" role="tab" aria-controls="pane-related" aria-selected="false">Related</button>
                </li>
            </ul>

            <div class="tab-content">
                {{-- Overview --}}
                <div class="tab-pane fade show active" id="pane-overview" role="tabpanel" aria-labelledby="tab-overview" tabindex="0">
                    @if($trailerEmbed)
                        <div class="fh-card mb-4" id="trailer">
                            <div class="ratio ratio-16x9">
                                <iframe
                                    src="{{ $trailerEmbed }}"
                                    title="Trailer for {{ $content->title }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </div>
                    @endif

                    <div class="fh-card mb-4">
                        <p class="fh-eyebrow">About this title</p>

                        @if($content->description)
                            <p class="mb-3">{!! nl2br(e($content->description)) !!}</p>
                        @endif

                        @if($content->synopsis)
                            <p class="fh-text-muted-2 mb-0">{!! nl2br(e($content->synopsis)) !!}</p>
                        @elseif(! $content->description)
                            <p class="fh-text-muted-2 mb-0">The full write-up for this title is on its way — check back soon.</p>
                        @endif
                    </div>

                    <div class="fh-card">
                        <p class="fh-eyebrow">Details</p>
                        <div class="fh-table-responsive">
                            <table class="fh-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">Release date</th>
                                        <td>{{ $content->release_date ? $content->release_date->format('F j, Y') : 'To be announced' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Type</th>
                                        <td>{{ $content->content_type ?: 'Other' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Genre</th>
                                        <td>{{ $content->genre ?: 'Various' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Category</th>
                                        <td>
                                            @if($content->category)
                                                <a href="{{ route('category.show', $content->category->slug) }}">{{ $content->category->name }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Status</th>
                                        <td>{{ ucfirst($content->status) }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Popularity</th>
                                        <td>{{ number_format((int) $content->popularity) }} / 100</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Rating</th>
                                        <td>{{ number_format((float) $content->rating, 1) }} / 10</td>
                                    </tr>
                                    @if($content->tags->isNotEmpty())
                                        <tr>
                                            <th scope="row">Tags</th>
                                            <td>
                                                <span class="d-flex flex-wrap gap-2">
                                                    @foreach($content->tags as $tag)
                                                        <x-badge variant="cyan">{{ $tag->name }}</x-badge>
                                                    @endforeach
                                                </span>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Characters --}}
                <div class="tab-pane fade" id="pane-characters" role="tabpanel" aria-labelledby="tab-characters" tabindex="0">
                    @if($characters->isEmpty())
                        <x-empty
                            title="No characters linked yet"
                            message="The cast list for {{ $content->title }} will appear here."
                            icon="bi-people"
                        />
                    @else
                        <div class="fh-grid fh-character-grid">
                            @foreach($characters as $item)
                                <div>
                                    <x-character-card :item="$item" />
                                    @if($item->pivot?->role)
                                        <p class="fh-text-dim fh-text-sm mt-2 mb-0 text-center">
                                            Role: {{ ucfirst($item->pivot->role) }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Media --}}
                <div class="tab-pane fade" id="pane-media" role="tabpanel" aria-labelledby="tab-media" tabindex="0">
                    @if($relatedMedia->isEmpty())
                        <x-empty
                            title="No media yet"
                            message="Trailers, clips and galleries for {{ $categoryName ?? 'this fandom' }} will show up here."
                            icon="bi-play-circle"
                        />
                    @else
                        <p class="fh-text-dim fh-text-sm">
                            <i class="bi bi-collection-play" aria-hidden="true"></i>
                            More {{ $categoryName ?? 'fandom' }} media — grouped by category.
                        </p>
                        <div class="fh-grid">
                            @foreach($relatedMedia as $item)
                                <x-media-card :item="$item" />
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Related --}}
                <div class="tab-pane fade" id="pane-related" role="tabpanel" aria-labelledby="tab-related" tabindex="0">
                    @if($related->isEmpty())
                        <x-empty
                            title="Nothing related yet"
                            message="Similar titles will be recommended here as the catalogue grows."
                            icon="bi-diagram-3"
                        >
                            <a class="btn btn-fh" href="{{ route('explore') }}">Browse the catalogue</a>
                        </x-empty>
                    @else
                        <div class="fh-grid">
                            @foreach($related as $item)
                                <x-content-card :item="$item" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="col-12 col-xl-4" aria-label="Quick facts and recommendations">
            <div class="fh-card mb-4">
                <p class="fh-panel-title"><i class="bi bi-clipboard-data" aria-hidden="true"></i> Quick facts</p>
                <div class="fh-table-responsive">
                    <table class="fh-table">
                        <tbody>
                            <tr>
                                <th scope="row">Category</th>
                                <td>{{ $categoryName ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Type</th>
                                <td>{{ $content->content_type ?: 'Other' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Genre</th>
                                <td>{{ $content->genre ?: 'Various' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Released</th>
                                <td>{{ $content->release_date ? $content->release_date->format('M j, Y') : 'TBA' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Views</th>
                                <td>{{ number_format((int) $content->views_count) }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Rating</th>
                                <td>{{ number_format((float) $content->rating, 1) }} / 10</td>
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td>{{ ucfirst($content->status) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="fh-card">
                <p class="fh-panel-title"><i class="bi bi-stars" aria-hidden="true"></i> More like this</p>

                @if($miniRelated->isEmpty())
                    <p class="fh-text-dim fh-text-sm mb-0">
                        Recommendations appear here once more titles share this category.
                    </p>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($miniRelated as $item)
                            <div class="d-flex align-items-center gap-3">
                                <a href="{{ route('content.show', $item->slug) }}" class="fh-media fh-media-16x9 flex-shrink-0" style="width: 96px;" aria-label="Open {{ $item->title }}">
                                    <img
                                        class="fh-img"
                                        src="{{ img($item->cover_image, $item->title, 320, 180, $item->category?->slug) }}"
                                        alt="{{ $item->title }}"
                                        width="320"
                                        height="180"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </a>
                                <div class="min-0 flex-grow-1">
                                    <a class="fh-fw-600 fh-clamp-2" href="{{ route('content.show', $item->slug) }}" style="text-decoration: none; color: inherit; font-size: .95rem;">
                                        {{ $item->title }}
                                    </a>
                                    <p class="fh-text-dim fh-text-sm mb-0">
                                        {{ $item->category?->name ?? 'Fandom' }} · {{ number_format((int) $item->views_count) }} views
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <a class="btn btn-fh-ghost w-100 mt-3" href="{{ route('explore', ['category' => $content->category?->slug ?? '']) }}">
                        See everything in {{ $categoryName ?? 'this fandom' }}
                    </a>
                @endif
            </div>
        </aside>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            // Copy-link share buttons (no global handler in assets/js/app.js).
            document.querySelectorAll('[data-share]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var url = window.location.href;
                    var done = function () {
                        if (window.FanHub && FanHub.toast) {
                            FanHub.toast('success', 'Link copied to your clipboard.');
                        }
                    };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(done).catch(function () { fallback(url, done); });
                    } else {
                        fallback(url, done);
                    }
                });
            });

            function fallback(text, done) {
                try {
                    var area = document.createElement('textarea');
                    area.value = text;
                    area.setAttribute('readonly', '');
                    area.style.position = 'fixed';
                    area.style.opacity = '0';
                    document.body.appendChild(area);
                    area.select();
                    document.execCommand('copy');
                    document.body.removeChild(area);
                    done();
                } catch (e) {
                    if (window.FanHub && FanHub.toast) {
                        FanHub.toast('error', 'Copying failed — please copy the address bar link.');
                    }
                }
            }

            // Trailer button: activate the Overview tab, then jump to the embed.
            var trailerBtn = document.getElementById('watch-trailer');
            var trailer = document.getElementById('trailer');
            if (trailerBtn && trailer) {
                trailerBtn.addEventListener('click', function () {
                    var pane = document.getElementById('pane-overview');
                    if (pane && window.bootstrap) {
                        var tab = document.getElementById('tab-overview');
                        if (tab) new bootstrap.Tab(tab).show();
                    }
                    trailer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            }
        })();
    </script>
@endsection
