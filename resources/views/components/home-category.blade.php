@props(['item'])

@php
    $slug = (string) data_get($item, 'slug');
    $name = (string) data_get($item, 'name');
    $icon = (string) (data_get($item, 'icon') ?: 'bi-collection');
    $icon = str_starts_with($icon, 'bi-') ? $icon : 'bi-'.$icon;

    $looks = [
        'anime' => ['grad' => 'linear-gradient(160deg, #fb7185 0%, #db2777 100%)', 'glow' => 'rgba(236, 72, 153, 0.45)'],
        'gaming' => ['grad' => 'linear-gradient(160deg, #60a5fa 0%, #2563eb 100%)', 'glow' => 'rgba(59, 130, 246, 0.45)'],
        'movies' => ['grad' => 'linear-gradient(160deg, #fb923c 0%, #ea580c 100%)', 'glow' => 'rgba(249, 115, 22, 0.42)'],
        'tv-shows' => ['grad' => 'linear-gradient(160deg, #34d399 0%, #059669 100%)', 'glow' => 'rgba(16, 185, 129, 0.4)'],
        'k-pop' => ['grad' => 'linear-gradient(160deg, #c084fc 0%, #7c3aed 100%)', 'glow' => 'rgba(139, 92, 246, 0.45)'],
        'comics' => ['grad' => 'linear-gradient(160deg, #fbbf24 0%, #d97706 100%)', 'glow' => 'rgba(245, 158, 11, 0.4)'],
        'manga' => ['grad' => 'linear-gradient(160deg, #22d3ee 0%, #0891b2 100%)', 'glow' => 'rgba(6, 182, 212, 0.4)'],
        'cosplay' => ['grad' => 'linear-gradient(160deg, #a78bfa 0%, #6d28d9 100%)', 'glow' => 'rgba(124, 58, 237, 0.45)'],
    ];

    $look = $looks[$slug] ?? ['grad' => 'linear-gradient(160deg, #a78bfa, #6d28d9)', 'glow' => 'rgba(139, 92, 246, 0.4)'];
    $cover = img(data_get($item, 'image'), $name, 320, 240, $slug);
    $count = (int) data_get($item, 'content_count', 0);
    $countLabel = $count === 1 ? '1 title' : number_format($count).' titles';
@endphp

<a class="fh-cat-tile" href="{{ route('category.show', $slug) }}" style="--tile: {{ $look['grad'] }}; --tile-glow: {{ $look['glow'] }}; --tile-img: url('{{ $cover }}');">
    <span class="fh-cat-tile-icon" aria-hidden="true">
        <i class="bi {{ $icon }}"></i>
    </span>
    <span class="fh-cat-tile-name">{{ $name }}</span>
    <span class="fh-cat-tile-count">{{ $countLabel }}</span>
</a>
