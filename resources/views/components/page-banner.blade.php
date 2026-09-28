@props([
    'title',
    'kicker' => null,
    'subtitle' => null,
    'image',
])

<header {{ $attributes->merge(['class' => 'fh-page-banner']) }} style="--banner: url('{{ $image }}')">
    <div class="fh-page-banner-copy">
        @if($kicker)
            <p class="fh-eyebrow">{{ $kicker }}</p>
        @endif
        <h1 class="fh-page-title">{{ $title }}</h1>
        @if($subtitle)
            <p class="fh-page-sub">{{ $subtitle }}</p>
        @endif
        @if(trim($slot) !== '')
            <div class="fh-page-banner-extra">{{ $slot }}</div>
        @endif
    </div>
</header>
