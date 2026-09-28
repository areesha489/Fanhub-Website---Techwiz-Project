@extends('layouts.admin')

@section('title', 'Members · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Members</h1>
            <p class="fh-page-sub">Search accounts, adjust roles and suspend or reactivate members.</p>
        </div>
        <a class="btn btn-fh" href="{{ route('admin.users.create') }}">
            <i class="bi bi-person-plus-fill me-2" aria-hidden="true"></i>Add member
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.users.index'),
            'searchPlaceholder' => 'Search name or email…',
            'filters' => [
                ['name' => 'role', 'label' => 'Role', 'value' => $role, 'options' => ['user' => 'Member', 'admin' => 'Admin']],
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => ['active' => 'Active', 'suspended' => 'Suspended']],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $userColumns = [
                    ['key' => null, 'label' => 'Member', 'format' => function ($row) {
                        $avatar = view('components.avatar', ['user' => $row, 'size' => 'sm'])->render();

                        return '<div class="d-flex align-items-center gap-2">'.$avatar.'<span>'.e($row->name).'</span></div>';
                    }],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'role', 'label' => 'Role', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->role])->render()],
                    ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                    ['key' => 'created_at', 'label' => 'Joined', 'format' => fn ($row) => e($row->created_at?->format('d M Y'))],
                    ['key' => null, 'label' => 'Actions', 'class' => 'text-end', 'format' => fn ($row) => view('admin.partials.row-actions', [
                        'editRoute' => route('admin.users.edit', $row),
                        'deleteRoute' => route('admin.users.destroy', $row),
                        'label' => $row->name,
                        'confirm' => 'Delete member '.$row->name.'? Their content stays, but the account is removed.',
                    ])->render()],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$userColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No members match"
                message="Try a different search term, or clear the filters."
                icon="bi-people"
            >
                <a class="btn btn-fh-outline" href="{{ route('admin.users.index') }}">Clear filters</a>
            </x-empty>
        @endif
    </div>
@endsection
