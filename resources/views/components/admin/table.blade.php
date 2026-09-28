@props([
    'columns' => [],
    'items' => [],
    'caption' => null,
])

@php
    /**
     * Reusable dark admin table.
     *
     * $columns = [
     *     ['key' => 'name', 'label' => 'Name'],                 // escaped data_get() output
     *     ['key' => 'name', 'label' => 'Name', 'class' => 'w-25'],
     *     ['key' => null, 'label' => 'Actions', 'format' => fn ($row) => '<div>…</div>'],
     * ];
     *
     * `format` closures return raw HTML — escape any user data with e() inside them.
     */
    $adminColumns = collect($columns)->map(function ($column): array {
        if (is_string($column)) {
            $column = ['key' => $column, 'label' => \Illuminate\Support\Str::headline($column)];
        }

        return array_merge([
            'key' => null,
            'label' => '',
            'class' => '',
            'format' => null,
        ], $column);
    })->values();

    $adminColumnCount = max(1, $adminColumns->count());
@endphp

<div {{ $attributes->merge(['class' => 'table-responsive']) }}>
    <table class="table fh-table mb-0 align-middle">
        @if($caption)
            <caption class="pt-1 pb-2 text-end text-muted small">{{ $caption }}</caption>
        @endif

        <thead>
            <tr>
                @foreach($adminColumns as $adminColumn)
                    <th scope="col" class="{{ $adminColumn['class'] }}">{{ $adminColumn['label'] }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse($items as $adminRow)
                <tr>
                    @foreach($adminColumns as $adminColumn)
                        <td class="{{ $adminColumn['class'] }}">
                            @if($adminColumn['format'] instanceof \Closure)
                                {!! $adminColumn['format']($adminRow) !!}
                            @else
                                {{ data_get($adminRow, $adminColumn['key']) }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $adminColumnCount }}" class="text-center text-muted py-4">
                        No records match the current filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
