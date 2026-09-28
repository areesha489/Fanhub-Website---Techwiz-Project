@props([
    'value' => 0,
    'size' => 'md',
    'count' => null,
])

@php
    $ratingValue = max(0, min(10, (float) $value));
    $ratingFill = round(($ratingValue / 10) * 100, 1);
    $ratingSizeClass = match ($size) {
        'sm' => ' fh-rating-sm',
        'lg' => ' fh-rating-lg',
        default => '',
    };
@endphp

<span
    {{ $attributes->merge(['class' => 'fh-rating'.$ratingSizeClass]) }}
    role="img"
    aria-label="Rating {{ rtrim(rtrim(number_format($ratingValue, 1), '0'), '.') }} out of 10"
>
    <span class="fh-rating-stars" aria-hidden="true">
        ★★★★★
        <span class="fh-stars-fill" style="width: {{ $ratingFill }}%;">★★★★★</span>
    </span>

    <span class="fh-rating-value">{{ number_format($ratingValue, 1) }}</span>

    @if($count !== null)
        <span class="fh-rating-count">({{ number_format((int) $count) }})</span>
    @endif
</span>
