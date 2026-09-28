{{--
    Cinematic hero slider.
    Props: :slides => [['id','title','subtitle','description','category','image','cta','link'], …]
    Behaviour (assets/js/app.js): auto-advance 6s, pause on hover/focus, dots, swipe,
    disabled when the user prefers reduced motion.
--}}
@props([
    'slides' => [],
])

@php
    $heroSlides = collect($slides)->values()->filter(function ($slide) {
        return data_get($slide, 'title');
    })->values();

    $heroCount = $heroSlides->count();
@endphp

@if($heroCount > 0)
    <section
        {{ $attributes->merge([
            'class' => 'fh-hero',
            'data-hero' => 'true',
            'data-interval' => '6000',
            'role' => 'region',
            'aria-roledescription' => 'carousel',
            'aria-label' => 'Featured on Fan Hub+',
        ]) }}
    >
        <div class="fh-hero-slides">
            @foreach($heroSlides as $heroIndex => $slide)
                @php
                    $heroTitle = (string) data_get($slide, 'title');
                    $heroImage = data_get($slide, 'image')
                        ?: ph($heroTitle, 1600, 760, data_get($slide, 'category'));

                    $heroLink = data_get($slide, 'link') ?: route('explore');
                    $heroCta = data_get($slide, 'cta') ?: 'Explore Now';
                    $heroMark = mb_strtoupper(mb_substr((string) (data_get($slide, 'category') ?: 'FH'), 0, 2));
                @endphp

                <div
                    class="fh-heroslide{{ $heroIndex === 0 ? ' is-active' : '' }}"
                    role="group"
                    aria-roledescription="slide"
                    aria-label="Slide {{ $heroIndex + 1 }} of {{ $heroCount }}"
                    aria-hidden="{{ $heroIndex === 0 ? 'false' : 'true' }}"
                >
                    <div class="fh-hero-media">
                        <img
                            class="fh-img"
                            src="{{ $heroImage }}"
                            alt="{{ $heroTitle }}"
                            width="1600"
                            height="760"
                            loading="{{ $heroIndex === 0 ? 'eager' : 'lazy' }}"
                            decoding="async"
                        >
                    </div>

                    <div class="fh-hero-overlay" aria-hidden="true"></div>

                    <div class="fh-hero-body">
                        <span class="fh-featured-pill">Featured</span>

                        <h2 class="fh-hero-title">{{ $heroTitle }}</h2>

                        @if(data_get($slide, 'subtitle'))
                            <p class="fh-hero-hook">{{ data_get($slide, 'subtitle') }}</p>
                        @endif

                        @if(data_get($slide, 'description'))
                            <p class="fh-hero-desc fh-clamp-2">{{ data_get($slide, 'description') }}</p>
                        @endif

                        <div class="fh-hero-actions">
                            <a class="btn btn-fh" href="{{ $heroLink }}">
                                {{ $heroCta }}
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>

                    <div class="fh-hero-stamp" aria-hidden="true">
                        <span>{{ $heroMark }}</span>
                        <small>{{ data_get($slide, 'category') ?: 'Fan Hub+' }}</small>
                    </div>
                </div>
            @endforeach
        </div>

        @if($heroCount > 1)
            <div class="fh-hero-dots" role="tablist" aria-label="Choose a featured slide">
                @foreach($heroSlides as $heroDotIndex => $heroSlide)
                    <button
                        type="button"
                        class="fh-hero-dot"
                        role="tab"
                        aria-label="Go to slide {{ $heroDotIndex + 1 }}: {{ data_get($heroSlide, 'title') }}"
                        aria-current="{{ $heroDotIndex === 0 ? 'true' : 'false' }}"
                    ></button>
                @endforeach
            </div>

            <div class="fh-hero-controls">
                <button type="button" class="fh-hero-nav" data-hero-prev aria-label="Previous slide">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="fh-hero-nav" data-hero-next aria-label="Next slide">
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        @endif
    </section>
@endif
