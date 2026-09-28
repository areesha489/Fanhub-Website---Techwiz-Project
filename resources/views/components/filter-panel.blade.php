{{--
    Sticky filter sidebar. `id="x-filters"` is the hook used by the front-end JS.
    Usage: <x-filter-panel :action="route('explore')" title="Filters"> …fields… </x-filter-panel>
--}}
@props([
    'action' => null,
    'method' => 'GET',
    'title' => 'Filters',
])

<aside
    id="x-filters"
    {{ $attributes->merge(['class' => 'fh-filter-panel']) }}
    aria-label="{{ $title }}"
>
    <p class="fh-filter-title">
        <i class="bi bi-sliders" aria-hidden="true"></i> {{ $title }}
    </p>

    @if($action)
        <form action="{{ $action }}" method="{{ strtoupper($method) }}" id="x-filters-form">
            @if(strtoupper($method) !== 'GET')
                @csrf
            @endif

            {{ $slot }}
        </form>
    @else
        {{ $slot }}
    @endif
</aside>
