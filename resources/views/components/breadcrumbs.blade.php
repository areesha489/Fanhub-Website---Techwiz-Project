@props([
    'items' => [],
])

@if(count($items) > 0)
    <nav {{ $attributes->merge(['class' => 'fh-breadcrumbs']) }} aria-label="Breadcrumb">
        @foreach($items as $index => $crumb)
            @php
                $crumbLabel = data_get($crumb, 'label');
                $crumbUrl = data_get($crumb, 'to');
                $isLast = $index === count($items) - 1;
            @endphp

            @if($index > 0)
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            @endif

            @if($crumbUrl && ! $isLast)
                <a href="{{ $crumbUrl }}">{{ $crumbLabel }}</a>
            @elseif($isLast)
                <span aria-current="page">{{ $crumbLabel }}</span>
            @else
                <span>{{ $crumbLabel }}</span>
            @endif
        @endforeach
    </nav>
@endif
