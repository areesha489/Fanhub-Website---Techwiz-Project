@props([
    'value' => '',
    'placeholder' => 'Search…',
    'action',
    'method' => 'GET',
])

<form {{ $attributes->merge(['class' => 'fh-search', 'role' => 'search']) }}
      action="{{ $action }}"
      method="{{ strtoupper($method) }}">
    <label class="visually-hidden" for="fh-search-{{ md5($action) }}">Search</label>
    <i class="bi bi-search" aria-hidden="true"></i>
    <input
        type="search"
        name="q"
        id="fh-search-{{ md5($action) }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
    >
    <button class="btn btn-fh btn-sm" type="submit">
        <span class="d-none d-sm-inline">Search</span>
        <i class="bi bi-search d-sm-none" aria-hidden="true"></i>
    </button>
</form>
