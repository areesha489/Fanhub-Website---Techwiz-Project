@props(['item'])

@php
    $title = (string) (data_get($item, 'title') ?: data_get($item, 'name'));
    $slug = data_get($item, 'slug');
    $price = data_get($item, 'price');
    $currency = data_get($item, 'currency') ?: 'USD';
    $symbol = match (strtoupper((string) $currency)) {
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        default => strtoupper((string) $currency).' ',
    };
    $url = route('merchandise.show', $slug);
    $image = img(data_get($item, 'image'), $title, 240, 240, data_get($item, 'category.slug'));
@endphp

<a class="fh-rail-merch" href="{{ $url }}">
    <img src="{{ $image }}" alt="{{ $title }}" width="240" height="240" loading="lazy" decoding="async">
    <strong>{{ $title }}</strong>
    <span>
        @if($price !== null)
            {{ $symbol }}{{ number_format((float) $price, 2) }}
        @else
            Showcase
        @endif
    </span>
</a>
