@props([
    'paginator',
])

@php
    $headTotal = (int) $paginator->total();
    $headFrom = (int) $paginator->firstItem();
    $headTo = (int) $paginator->lastItem();
@endphp

@if($headTotal > 0)
    <p {{ $attributes->merge(['class' => 'mb-0 text-muted small']) }} role="status">
        Showing <strong class="text-body-emphasis">{{ number_format($headFrom) }}</strong>&ndash;<strong class="text-body-emphasis">{{ number_format($headTo) }}</strong>
        of <strong class="text-body-emphasis">{{ number_format($headTotal) }}</strong> {{ \Illuminate\Support\Str::plural('record', $headTotal) }}
    </p>
@endif
