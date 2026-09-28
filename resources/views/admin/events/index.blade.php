@extends('layouts.admin')

@section('title', 'Events · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Events</h1>
            <p class="fh-page-sub">Conventions, meetups, tournaments and screenings around the world.</p>
        </div>
        <a class="btn btn-fh" href="{{ route('admin.events.create') }}">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add event
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.events.index'),
            'searchPlaceholder' => 'Search name, venue or city…',
            'filters' => [
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => ['upcoming' => 'Upcoming', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'cancelled' => 'Cancelled']],
                ['name' => 'type', 'label' => 'Type', 'value' => $type, 'options' => collect($eventTypes)->mapWithKeys(fn ($t) => [$t => $t])->all()],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $eventColumns = [
                    ['key' => 'name', 'label' => 'Event', 'format' => function ($row) {
                        $link = e(route('admin.events.edit', $row));

                        return '<a href="'.$link.'" class="fh-table-title">'.e($row->name).'</a>'
                            .'<div class="small text-muted">'.e(trim(($row->venue ?: '').' '.($row->city ?: '')) ?: 'Venue TBC').'</div>';
                    }],
                    ['key' => 'category.name', 'label' => 'Category'],
                    ['key' => 'event_type', 'label' => 'Type', 'format' => fn ($row) => '<span class="fh-chip">'.e($row->event_type).'</span>'],
                    ['key' => 'start_date', 'label' => 'Starts', 'format' => fn ($row) => e($row->start_date?->format('d M Y') ?? '—')],
                    ['key' => 'city', 'label' => 'City', 'format' => fn ($row) => e($row->city ?: '—')],
                    ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                    ['key' => 'featured', 'label' => 'Featured', 'format' => fn ($row) => $row->featured ? '<i class="bi bi-star-fill text-warning" aria-label="Featured"></i>' : '<span class="text-muted">—</span>'],
                    ['key' => null, 'label' => 'Actions', 'class' => 'text-end', 'format' => fn ($row) => view('admin.partials.row-actions', [
                        'editRoute' => route('admin.events.edit', $row),
                        'deleteRoute' => route('admin.events.destroy', $row),
                        'label' => $row->name,
                        'confirm' => 'Delete "'.$row->name.'"? This cannot be undone.',
                    ])->render()],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$eventColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No events match"
                message="Adjust the filters, or add the first event."
                icon="bi-calendar-event"
            >
                <a class="btn btn-fh" href="{{ route('admin.events.create') }}">Add event</a>
            </x-empty>
        @endif
    </div>
@endsection
