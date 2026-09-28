@extends('layouts.admin')

@section('title', 'Releases · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Releases</h1>
            <p class="fh-page-sub">Upcoming seasons, volumes, patches and launch dates.</p>
        </div>
        <a class="btn btn-fh" href="{{ route('admin.releases.create') }}">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add release
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.releases.index'),
            'searchPlaceholder' => 'Search release title…',
            'filters' => [
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => ['upcoming' => 'Upcoming', 'released' => 'Released', 'delayed' => 'Delayed']],
                ['name' => 'type', 'label' => 'Type', 'value' => $type, 'options' => collect($releaseTypes)->mapWithKeys(fn ($t) => [$t => $t])->all()],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $releaseColumns = [
                    ['key' => 'title', 'label' => 'Release', 'format' => function ($row) {
                        $link = e(route('admin.releases.edit', $row));

                        return '<a href="'.$link.'" class="fh-table-title">'.e($row->title).'</a>'
                            .'<div class="small text-muted">'.e($row->content?->title ?? 'No linked title').'</div>';
                    }],
                    ['key' => 'release_type', 'label' => 'Type', 'format' => fn ($row) => '<span class="fh-chip">'.e($row->release_type).'</span>'],
                    ['key' => 'category.name', 'label' => 'Category', 'format' => fn ($row) => e($row->category?->name ?? '—')],
                    ['key' => 'release_date', 'label' => 'Release date', 'format' => fn ($row) => e($row->release_date?->format('d M Y') ?? '—')],
                    ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                    ['key' => 'popularity', 'label' => 'Popularity', 'format' => fn ($row) => number_format((int) $row->popularity)],
                    ['key' => null, 'label' => 'Actions', 'class' => 'text-end', 'format' => fn ($row) => view('admin.partials.row-actions', [
                        'editRoute' => route('admin.releases.edit', $row),
                        'deleteRoute' => route('admin.releases.destroy', $row),
                        'label' => $row->title,
                        'confirm' => 'Delete "'.$row->title.'"? This cannot be undone.',
                    ])->render()],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$releaseColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No releases match"
                message="Adjust the filters, or add the first release date."
                icon="bi-calendar-check"
            >
                <a class="btn btn-fh" href="{{ route('admin.releases.create') }}">Add release</a>
            </x-empty>
        @endif
    </div>
@endsection
