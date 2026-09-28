@props([
    'title',
    'subtitle' => null,
    'href' => null,
    'linkText' => null,
])

<section {{ $attributes->merge(['class' => 'fh-section']) }}>
    <div class="fh-section-head">
        <div class="fh-section-head-main">
            <span class="fh-section-bar" aria-hidden="true"></span>
            <div class="min-w-0">
                <h2 class="fh-section-title">{{ $title }}</h2>
                @if($subtitle)
                    <p class="fh-section-sub">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if($href)
            <a class="fh-section-link" href="{{ $href }}">
                {{ $linkText ?? 'View all' }}
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        @endif
    </div>

    {{ $slot }}
</section>
