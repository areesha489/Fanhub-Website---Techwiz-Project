@extends('layouts.app')

@section('title', $character?->name ? $character->name.' · Characters · Fan Hub+' : 'Characters · Fan Hub+')

@section('content')
@if($loadError !== null || $character === null)
    <x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Characters', 'to' => route('characters.index')]]" />
    <x-error :message="$loadError ?? 'We could not load this character profile right now. Please try again in a moment. You may have followed a link to a profile that no longer exists.'">
        <a class="btn btn-fh-ghost btn-sm" href="{{ route('characters.index') }}">
            <i class="bi bi-people" aria-hidden="true"></i> Back to characters
        </a>
    </x-error>
@else
    @php
        $characterCategory = $character->category;
        $portrait = img($character->image, $character->name, 600, 900, $characterCategory?->slug);
        $popularity = max(0, min(100, (int) $character->popularity));

        $bioParagraphs = collect(preg_split('/\R+/', (string) $character->bio))
            ->map(fn (string $paragraph): string => trim($paragraph))
            ->filter()
            ->values();

        if ($bioParagraphs->isEmpty()) {
            $bioParagraphs = collect([
                'This profile is still being written up by the Fan Hub+ editors. The full backstory, abilities and turning points will appear here as soon as the entry is finished.',
            ]);
        }

        $debutLabel = null;
        if (filled($character->debut)) {
            try {
                $debutLabel = \Illuminate\Support\Carbon::parse($character->debut)->format('M j, Y');
            } catch (\Throwable $exception) {
                $debutLabel = (string) $character->debut;
            }
        }

        $facts = [
            'Gender' => $character->gender,
            'Age' => $character->age,
            'Affiliation' => $character->affiliation,
            'Abilities' => $character->abilities,
            'Debut' => $debutLabel,
        ];

        $portrait = img($character->image, $character->name, 800, 1000, $characterCategory?->slug);
        $galleryImages = $gallery->isNotEmpty()
            ? $gallery->map(fn ($source): string => img(is_string($source) ? $source : null, $character->name, 800, 450, $characterCategory?->slug))->values()
            : collect([$portrait, $portrait, $portrait, $portrait]);

        $infoItems = array_filter([
            ['icon' => 'bi-gender-ambiguous', 'label' => 'Gender', 'value' => $character->gender],
            ['icon' => 'bi-calendar-heart', 'label' => 'Age', 'value' => $character->age],
            ['icon' => 'bi-people', 'label' => 'Affiliation', 'value' => $character->affiliation],
            ['icon' => 'bi-lightning-charge', 'label' => 'Abilities', 'value' => $character->abilities],
            ['icon' => 'bi-film', 'label' => 'Debut', 'value' => $debutLabel],
        ], fn (array $item): bool => filled($item['value']));
    @endphp

    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Characters', 'to' => route('characters.index')],
        ['label' => $character->name],
    ]" />

    <div class="fh-layout-with-aside fh-aside-right">
        <div class="min-w-0">
            <article class="fh-card fh-card-pad mb-4">
                <div class="row g-4 align-items-center">
                    <div class="col-auto">
                        <div class="fh-media fh-character-portrait" style="border-radius: var(--fh-radius-lg); overflow: hidden;">
                            <img
                                class="fh-img"
                                src="{{ $portrait }}"
                                alt="Portrait of {{ $character->name }}"
                                width="600"
                                height="900"
                                decoding="async"
                            >
                        </div>
                    </div>

                    <div class="col-12 col-sm-7 col-md-8 col-lg-9">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            @if($characterCategory)
                                <a class="fh-chip" href="{{ route('category.show', $characterCategory->slug) }}">
                                    <i class="bi {{ $characterCategory->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                                    {{ $characterCategory->name }}
                                </a>
                            @endif

                            @if($character->featured)
                                <x-badge variant="amber">Featured</x-badge>
                            @endif

                            @if($character->gender)
                                <x-badge variant="slate">{{ $character->gender }}</x-badge>
                            @endif
                        </div>

                        <h1 class="fh-title mb-1">{{ $character->name }}</h1>
                        <p class="fh-sub mb-3">{{ $character->fandom ?: ($characterCategory?->name ?? 'Fan Hub+ original') }}</p>

                        <div class="mb-3" style="max-width: 420px;">
                            <div class="d-flex align-items-center justify-content-between fh-text-sm mb-1">
                                <span class="fh-text-dim">Popularity</span>
                                <span class="fh-mono fh-fw-600">{{ $popularity }} / 100</span>
                            </div>
                            <div
                                class="fh-progress"
                                role="progressbar"
                                aria-valuenow="{{ $popularity }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-label="Popularity of {{ $character->name }}"
                            >
                                <div class="fh-progress-bar" style="width: {{ $popularity }}%;"></div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <x-bookmark
                                type="character"
                                :id="$character->id"
                                :bookmarked="$isBookmarked"
                                :bookmark-id="$bookmarkId"
                            />
                            <button
                                type="button"
                                class="btn btn-fh-outline btn-sm"
                                data-share-copy
                                aria-label="Share this character profile"
                            >
                                <i class="bi bi-share" aria-hidden="true"></i> Share
                            </button>
                        </div>
                    </div>
                </div>
            </article>

            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-4">
                    <x-stat-card label="Profile views" :value="number_format($stats['views'])" icon="bi-eye" tone="cyan" />
                </div>
                <div class="col-6 col-lg-4">
                    <x-stat-card label="Bookmarks" :value="number_format($stats['bookmarks'])" icon="bi-bookmark-star" tone="violet" />
                </div>
                <div class="col-12 col-lg-4">
                    <x-stat-card label="Fan rating" :value="number_format($stats['rating'], 1).' / 10'" icon="bi-star-fill" tone="amber" />
                </div>
            </div>

            <section class="fh-section mb-4">
                <div class="fh-section-head">
                    <div class="fh-section-head-main">
                        <span class="fh-section-bar" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <h2 class="fh-section-title">Biography</h2>
                            <p class="fh-section-sub">Where {{ $character->name }} comes from and what drives them</p>
                        </div>
                    </div>
                </div>

                <div class="fh-card fh-card-pad" style="max-width: 78ch;">
                    @foreach($bioParagraphs as $paragraph)
                        <p class="fh-text-muted-2">{{ $paragraph }}</p>
                    @endforeach
                </div>
            </section>

            <section class="fh-section mb-4">
                <div class="fh-section-head">
                    <div class="fh-section-head-main">
                        <span class="fh-section-bar" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <h2 class="fh-section-title">Character sheet</h2>
                            <p class="fh-section-sub">Core details at a glance</p>
                        </div>
                    </div>
                </div>

                <div class="fh-card fh-card-pad">
                    <div class="row g-3">
                        @forelse($infoItems as $infoItem)
                            <div class="col-6 col-lg-4">
                                <div class="fh-text-dim fh-text-sm">
                                    <i class="bi {{ $infoItem['icon'] }}" aria-hidden="true"></i>
                                    {{ $infoItem['label'] }}
                                </div>
                                <div class="fh-fw-600">{{ $infoItem['value'] }}</div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="fh-text-dim mb-0">The editors have not filled in the character sheet yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="fh-section mb-4">
                <div class="fh-section-head">
                    <div class="fh-section-head-main">
                        <span class="fh-section-bar" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <h2 class="fh-section-title">Gallery</h2>
                            <p class="fh-section-sub">{{ $galleryImages->count() }} stills and portraits</p>
                        </div>
                    </div>
                </div>

                <div class="fh-grid fh-character-grid">
                    @foreach($galleryImages as $index => $galleryImage)
                        <button
                            type="button"
                            class="fh-card fh-card-hover p-0 border-0 text-start"
                            data-lightbox
                            data-lightbox-src="{{ $galleryImage }}"
                            data-lightbox-alt="{{ $character->name }} gallery image {{ $index + 1 }}"
                            aria-label="Open {{ $character->name }} gallery image {{ $index + 1 }}"
                            style="overflow: hidden;"
                        >
                            <div class="fh-media fh-media-16x9">
                                <img
                                    class="fh-img"
                                    src="{{ $galleryImage }}"
                                    alt="{{ $character->name }} gallery image {{ $index + 1 }}"
                                    width="800"
                                    height="450"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>
                        </button>
                    @endforeach
                </div>
            </section>

            @if($relatedCharacters->isNotEmpty())
                <x-section title="Related Characters" subtitle="Allies, rivals and every other link in the story">
                    <div class="fh-grid fh-character-grid">
                        @foreach($relatedCharacters as $relatedCharacter)
                            @php
                                $relationLabel = data_get($relatedCharacter, 'pivot.relation') ?: 'Same fandom';
                            @endphp
                            <div class="h-100">
                                <span class="fh-chip fh-chip-cyan mb-2">
                                    <i class="bi bi-diagram-3" aria-hidden="true"></i> {{ ucfirst($relationLabel) }}
                                </span>
                                <x-character-card :item="$relatedCharacter" />
                            </div>
                        @endforeach
                    </div>
                </x-section>
            @endif

            @if($relatedContents->isNotEmpty())
                <x-section
                    title="Related Content"
                    subtitle="Titles where {{ $character->name }} shows up"
                    :href="$characterCategory ? route('category.show', $characterCategory->slug) : null"
                    :linkText="$characterCategory ? 'More from '.$characterCategory->name : null"
                >
                    <div class="fh-grid fh-grid-wide">
                        @foreach($relatedContents as $relatedContent)
                            <x-content-card :item="$relatedContent" />
                        @endforeach
                    </div>
                </x-section>
            @endif
        </div>

        <aside class="d-flex flex-column gap-3" aria-label="Character sidebar">
            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-collection-fill" aria-hidden="true"></i> Fandom
                </p>

                @if($characterCategory)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fh-icon-tile" aria-hidden="true">
                            <i class="bi {{ $characterCategory->icon ?: 'bi-collection-fill' }}"></i>
                        </span>
                        <div class="min-w-0">
                            <a class="fh-fw-600" href="{{ route('category.show', $characterCategory->slug) }}">{{ $characterCategory->name }}</a>
                            <p class="fh-text-dim fh-text-sm mb-0 fh-clamp-2">{{ $characterCategory->description }}</p>
                        </div>
                    </div>
                @endif

                <p class="fh-text-muted-2 fh-text-sm">
                    <strong>{{ $character->name }}</strong> belongs to
                    <em>{{ $character->fandom ?: ($characterCategory?->name ?? 'this fandom') }}</em>.
                </p>

                @if($characterCategory)
                    <a class="fh-card-cta" href="{{ route('category.show', $characterCategory->slug) }}">
                        Explore {{ $characterCategory->name }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </div>

            <div class="fh-card fh-card-pad">
                <p class="fh-filter-title">
                    <i class="bi bi-compass" aria-hidden="true"></i> Keep exploring
                </p>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li>
                        <a class="fh-card-cta" href="{{ route('characters.index') }}">
                            <i class="bi bi-people" aria-hidden="true"></i> All characters
                        </a>
                    </li>
                    <li>
                        <a class="fh-card-cta" href="{{ route('articles.index') }}">
                            <i class="bi bi-newspaper" aria-hidden="true"></i> Latest articles
                        </a>
                    </li>
                    <li>
                        <a class="fh-card-cta" href="{{ route('media.index') }}">
                            <i class="bi bi-play-circle" aria-hidden="true"></i> Multimedia centre
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
    </div>

    <div class="fh-modal-host">
        <div class="modal fade" id="character-gallery-modal" tabindex="-1" aria-labelledby="character-gallery-label" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content fh-glass">
                    <div class="modal-header">
                        <h2 class="modal-title fh-text-sm" id="character-gallery-label">{{ $character->name }} — gallery</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close gallery"></button>
                    </div>
                    <div class="modal-body text-center p-2">
                        <img id="character-gallery-image" class="fh-img w-100" src="{{ $galleryImages->first() }}" alt="" width="800" height="450" decoding="async">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    (function () {
        var modalImage = document.getElementById('character-gallery-image');
        var modalElement = document.getElementById('character-gallery-modal');

        document.querySelectorAll('[data-lightbox]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!modalImage || !modalElement) return;
                modalImage.src = button.getAttribute('data-lightbox-src') || '';
                modalImage.alt = button.getAttribute('data-lightbox-alt') || '';
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
