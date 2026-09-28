@props(['item'])

@php
    $categoryName = (string) data_get($item, 'name');
    $categorySlug = data_get($item, 'slug');
    $categoryIcon = data_get($item, 'icon');
    $categoryIcon = $categoryIcon
        ? (str_starts_with($categoryIcon, 'bi-') ? $categoryIcon : 'bi-'.$categoryIcon)
        : 'bi-collection-fill';

    $categoryAccent = (string) data_get($item, 'accent');
    $categoryAccent = preg_match('/^#[0-9a-fA-F]{3,8}$/', $categoryAccent) === 1
        ? $categoryAccent
        : '#8B5CF6';

    $categoryCount = data_get($item, 'content_count');
    if ($categoryCount === null) {
        $categoryCount = data_get($item, 'contents_count', 0);
    }
@endphp

<article {{ $attributes->merge(['class' => 'fh-card fh-card-hover fh-category-card']) }}>
    <span
        class="fh-icon-tile"
        style="background: linear-gradient(135deg, {{ $categoryAccent }}, {{ $categoryAccent }}99); box-shadow: 0 10px 24px {{ $categoryAccent }}45;"
        aria-hidden="true"
    >
        <i class="bi {{ $categoryIcon }}"></i>
    </span>

    <div class="min-w-0 flex-grow-1">
        <h3 class="fh-cat-name fh-clamp-2">
            <a class="stretched-link" href="{{ route('category.show', $categorySlug) }}">
                {{ $categoryName }}
            </a>
        </h3>
        <p class="fh-cat-count mb-0">
            {{ number_format((int) $categoryCount) }} {{ ((int) $categoryCount) === 1 ? 'title' : 'titles' }}
        </p>
    </div>

    <i class="bi bi-arrow-right fh-cat-arrow" aria-hidden="true"></i>
</article>
