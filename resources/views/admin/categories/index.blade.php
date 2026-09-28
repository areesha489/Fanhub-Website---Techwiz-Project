@extends('layouts.admin')

@section('title', 'Categories · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Categories</h1>
            <p class="fh-page-sub">Fandom buckets that drive the sidebar, home page and content grouping.</p>
        </div>
        <a class="btn btn-fh" href="{{ route('admin.categories.create') }}">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add category
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.categories.index'),
            'searchPlaceholder' => 'Search name or description…',
            'filters' => [],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $categoryColumns = [
                    ['key' => null, 'label' => 'Category', 'format' => function ($row) {
                        $icon = $row->icon ?: 'bi-folder';
                        $swatch = $row->accent ? 'background: '.e($row->accent).';' : 'background: rgba(139,92,246,.35);';

                        return '<div class="d-flex align-items-center gap-2">'
                            .'<span class="fh-avatar" style="'.$swatch.' display:inline-flex; align-items:center; justify-content:center;"><i class="bi '.e($icon).'" aria-hidden="true"></i></span>'
                            .'<div><div>'.e($row->name).'</div>'
                            .'<small class="text-muted">'.e($row->slug).'</small></div></div>';
                    }],
                    ['key' => 'description', 'label' => 'Description', 'format' => fn ($row) => e(\Illuminate\Support\Str::limit((string) $row->description, 70))],
                    ['key' => 'icon', 'label' => 'Icon', 'format' => fn ($row) => '<code>'.e($row->icon ?: '—').'</code>'],
                    ['key' => 'contents_count', 'label' => 'Content', 'format' => fn ($row) => '<span class="fh-chip">'.number_format((int) $row->contents_count).' items</span>'],
                    ['key' => 'position', 'label' => 'Position'],
                    ['key' => null, 'label' => 'Actions', 'class' => 'text-end', 'format' => fn ($row) => view('admin.partials.row-actions', [
                        'editRoute' => route('admin.categories.edit', $row),
                        'deleteRoute' => route('admin.categories.destroy', $row),
                        'label' => $row->name,
                        'confirm' => 'Delete '.$row->name.'? All content in this category is removed too.',
                    ])->render()],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$categoryColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No categories yet"
                message="Create the first fandom bucket to start organising the catalogue."
                icon="bi-bookmark"
            >
                <a class="btn btn-fh" href="{{ route('admin.categories.create') }}">Add category</a>
            </x-empty>
        @endif
    </div>
@endsection
