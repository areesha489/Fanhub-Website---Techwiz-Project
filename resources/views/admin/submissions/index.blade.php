@extends('layouts.admin')

@section('title', 'Submissions · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Submissions</h1>
            <p class="fh-page-sub">
                Fan-submitted articles, artwork and videos waiting for a verdict.
                @if($pendingCount > 0)
                    <x-badge variant="amber">{{ $pendingCount }} pending</x-badge>
                @endif
            </p>
        </div>
        <a class="btn btn-fh-outline" href="{{ route('admin.chatbot.queries') }}">
            <i class="bi bi-clock-history me-2" aria-hidden="true"></i>Chatbot log
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.submissions.index'),
            'searchPlaceholder' => 'Search title or description…',
            'filters' => [
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $submissionColumns = [
                    ['key' => 'title', 'label' => 'Submission', 'format' => function ($row) {
                        $link = $row->url ? '<div class="small"><a href="'.e($row->url).'" target="_blank" rel="noopener noreferrer">'.e(\Illuminate\Support\Str::limit((string) $row->url, 45)).' <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a></div>' : '';

                        return '<div class="fh-table-title">'.e($row->title).'</div>'
                            .'<div class="small text-muted">'.e(ucfirst($row->type)).' · '.e($row->category?->name ?? 'No category').'</div>'
                            .$link;
                    }],
                    ['key' => 'user.name', 'label' => 'Member', 'format' => fn ($row) => e($row->user?->name ?? 'Deleted user')],
                    ['key' => 'description', 'label' => 'Description', 'format' => fn ($row) => e(\Illuminate\Support\Str::limit((string) $row->description, 90))],
                    ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                    ['key' => 'created_at', 'label' => 'Submitted', 'format' => fn ($row) => e($row->created_at?->format('d M Y H:i'))],
                    ['key' => null, 'label' => 'Review', 'class' => 'submission-review-cell', 'format' => function ($row) {
                        return view('admin.partials.submission-review', ['row' => $row])->render();
                    }],
                    ['key' => null, 'label' => 'Actions', 'class' => 'text-end', 'format' => fn ($row) => view('admin.partials.row-actions', [
                        'deleteOnly' => true,
                        'deleteRoute' => route('admin.submissions.destroy', $row),
                        'label' => $row->title,
                        'confirm' => 'Delete submission "'.$row->title.'"? This cannot be undone.',
                    ])->render()],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$submissionColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="Nothing to review"
                message="Submissions from members will queue up here."
                icon="bi-inbox"
            />
        @endif
    </div>
@endsection

@section('styles')
<style>
    .submission-review-cell {
        min-width: 240px;
    }
    .submission-review-cell .fh-field {
        min-height: 34px;
        padding: .3rem .6rem;
        font-size: .85rem;
    }
</style>
@endsection
