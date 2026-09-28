@extends('layouts.admin')

@section('title', 'Merchandise · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Merchandise</h1>
            <p class="fh-page-sub">Collectibles, figures and drops with pricing and release dates.</p>
        </div>
        <a class="btn btn-fh" href="{{ route('admin.merchandise.create') }}">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add item
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.merchandise.index'),
            'searchPlaceholder' => 'Search title or fandom…',
            'filters' => [
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']],
                ['name' => 'upcoming', 'label' => 'Availability', 'value' => $upcoming, 'options' => ['1' => 'Upcoming only', '0' => 'Available now']],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $merchColumns = [
                    ['key' => null, 'label' => '', 'class' => 'p-0', 'format' => function ($row) {
                        $src = $row->image ? asset('storage/'.ltrim($row->image, '/')) : img(null, $row->title, 120, 120, 'merch');

                        return '<img src="'.e($src).'" alt="" width="44" height="44" loading="lazy" decoding="async" class="fh-img" style="object-fit: cover; border-radius: .6rem; display: block; margin: .4rem 0;">';
                    }],
                    ['key' => 'title', 'label' => 'Item', 'format' => function ($row) {
                        $link = e(route('admin.merchandise.edit', $row));

                        return '<a href="'.$link.'" class="fh-table-title">'.e($row->title).'</a>'
                            .'<div class="small text-muted">'.e($row->fandom ?: 'No fandom').'</div>';
                    }],
                    ['key' => 'category.name', 'label' => 'Category'],
                    ['key' => 'price', 'label' => 'Price', 'format' => fn ($row) => $row->price !== null ? e($row->currency).' '.e(number_format((float) $row->price, 2)) : '<span class="text-muted">—</span>'],
                    ['key' => 'release_date', 'label' => 'Release', 'format' => function ($row) {
                        $line = e($row->release_date?->format('d M Y') ?? '—');

                        if ($row->is_upcoming) {
                            $line .= ' <span class="fh-chip">soon</span>';
                        }

                        return $line;
                    }],
                    ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                    ['key' => 'popularity', 'label' => 'Popularity', 'format' => fn ($row) => number_format((int) $row->popularity)],
                    ['key' => null, 'label' => 'Actions', 'class' => 'text-end', 'format' => fn ($row) => view('admin.partials.row-actions', [
                        'editRoute' => route('admin.merchandise.edit', $row),
                        'deleteRoute' => route('admin.merchandise.destroy', $row),
                        'label' => $row->title,
                        'confirm' => 'Delete "'.$row->title.'"? This cannot be undone.',
                    ])->render()],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$merchColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No merchandise matches"
                message="Adjust the filters, or add the first product."
                icon="bi-bag"
            >
                <a class="btn btn-fh" href="{{ route('admin.merchandise.create') }}">Add item</a>
            </x-empty>
        @endif
    </div>
@endsection
