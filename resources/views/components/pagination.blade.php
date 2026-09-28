@props([
    'paginator',
])

@if($paginator->hasPages())
    <nav {{ $attributes->merge(['class' => 'fh-pagination']) }} aria-label="Pagination results">
        {{ $paginator->links() }}
    </nav>
@endif
