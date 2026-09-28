{{--
    Uniform admin filter bar: one GET form carrying the free-text search (`q`),
    any dropdown filters and the sort control, plus a reset link.
    Keeps every index page identical in structure and query-string behaviour.

    @include('admin.partials.filter-bar', [
        'action'          => route('admin.content.index'),
        'searchPlaceholder' => 'Search title, genre…',
        'filters'         => [['name' => 'status', 'label' => 'Status', 'value' => request('status'), 'options' => [...]]],
        'sortOptions'     => ['newest' => 'Newest first', ...],
        'sortValue'       => request('sort', 'newest'),
    ])
--}}

@php
    $barSearchValue = $searchValue ?? request('q', '');
    $barFilters = $filters ?? [];
    $barSortOptions = $sortOptions ?? [];
    $barSortValue = $sortValue ?? '';
    $barHasQuery = $barSearchValue !== ''
        || $barFilters !== []
        || ($barSortOptions !== [] && $barSortValue !== array_key_first($barSortOptions) && $barSortValue !== '');
@endphp

<form method="GET" action="{{ $action }}" class="fh-toolbar" role="search">
    <div class="fh-search">
        <label class="visually-hidden" for="fh-admin-search">Search</label>
        <i class="bi bi-search" aria-hidden="true"></i>
        <input
            type="search"
            name="q"
            id="fh-admin-search"
            value="{{ $barSearchValue }}"
            placeholder="{{ $searchPlaceholder ?? 'Search…' }}"
            autocomplete="off"
        >
        <button class="btn btn-fh btn-sm" type="submit">
            <span class="d-none d-sm-inline">Apply</span>
            <i class="bi bi-sliders d-sm-none" aria-hidden="true"></i>
        </button>
    </div>

    @foreach($barFilters as $barFilter)
        <label class="visually-hidden" for="fh-admin-filter-{{ $barFilter['name'] }}">{{ $barFilter['label'] }}</label>
        <select
            class="fh-field fh-admin-filter"
            name="{{ $barFilter['name'] }}"
            id="fh-admin-filter-{{ $barFilter['name'] }}"
        >
            <option value="">{{ $barFilter['label'] }}: All</option>
            @foreach($barFilter['options'] as $barValue => $barLabel)
                <option value="{{ $barValue }}" @selected((string) ($barFilter['value'] ?? '') === (string) $barValue)>
                    {{ $barLabel }}
                </option>
            @endforeach
        </select>
    @endforeach

    @if($barSortOptions !== [])
        <label class="visually-hidden" for="fh-admin-sort">Sort by</label>
        <select class="fh-field fh-admin-filter" name="sort" id="fh-admin-sort">
            @foreach($barSortOptions as $barValue => $barLabel)
                <option value="{{ $barValue }}" @selected($barSortValue === (string) $barValue)>
                    {{ $barLabel }}
                </option>
            @endforeach
        </select>
    @endif

    @if($barHasQuery)
        <a class="btn btn-fh-ghost btn-sm" href="{{ $action }}" aria-label="Clear all filters">
            <i class="bi bi-x-circle me-1" aria-hidden="true"></i>Reset
        </a>
    @endif
</form>
