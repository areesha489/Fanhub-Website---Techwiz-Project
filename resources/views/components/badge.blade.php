@props([
    'variant' => 'violet',
    'pill' => true,
])

@php
    $allowed = ['violet', 'pink', 'cyan', 'green', 'amber', 'red', 'slate'];
    $badgeVariant = in_array($variant, $allowed, true) ? $variant : 'violet';
@endphp

<span
    {{ $attributes->merge([
        'class' => 'fh-badge fh-badge-'.$badgeVariant.($pill ? ' rounded-pill' : ' fh-badge-square'),
    ]) }}
>{{ $slot }}</span>
