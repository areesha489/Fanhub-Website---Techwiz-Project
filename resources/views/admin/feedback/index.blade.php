@extends('layouts.admin')

@section('title', 'Feedback · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Feedback inbox</h1>
            <p class="fh-page-sub">
                Bugs, suggestions and questions from the community.
                @if($openCount > 0)
                    <x-badge variant="red">{{ $openCount }} open</x-badge>
                @endif
            </p>
        </div>
        <a class="btn btn-fh-outline" href="{{ route('admin.chatbot.queries') }}">
            <i class="bi bi-chat-dots me-2" aria-hidden="true"></i>Chatbot log
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.feedback.index'),
            'searchPlaceholder' => 'Search subject or message…',
            'filters' => [
                ['name' => 'type', 'label' => 'Type', 'value' => $type, 'options' => ['bug' => 'Bug', 'suggestion' => 'Suggestion', 'query' => 'Query']],
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => ['open' => 'Open', 'in_review' => 'In review', 'resolved' => 'Resolved', 'closed' => 'Closed']],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $feedbackColumns = [
                    ['key' => 'subject', 'label' => 'Feedback', 'format' => function ($row) {
                        return '<div class="fh-table-title">'.e($row->subject).'</div>'
                            .'<div class="small text-muted">'.e(ucfirst($row->type)).' from '.e($row->user?->name ?? 'a guest').'</div>'
                            .'<div class="small">'.e(\Illuminate\Support\Str::limit((string) $row->message, 110)).'</div>';
                    }],
                    ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                    ['key' => 'created_at', 'label' => 'Received', 'format' => fn ($row) => e($row->created_at?->format('d M Y H:i'))],
                    ['key' => null, 'label' => 'Respond', 'class' => 'feedback-reply-cell', 'format' => fn ($row) => view('admin.partials.feedback-reply', ['row' => $row])->render()],
                    ['key' => null, 'label' => 'Actions', 'class' => 'text-end', 'format' => fn ($row) => view('admin.partials.row-actions', [
                        'deleteOnly' => true,
                        'deleteRoute' => route('admin.feedback.destroy', $row),
                        'label' => $row->subject,
                        'confirm' => 'Delete feedback "'.$row->subject.'"? This cannot be undone.',
                    ])->render()],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$feedbackColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No feedback matches"
                message="When members send feedback it will land in this inbox."
                icon="bi-chat-quote"
            />
        @endif
    </div>
@endsection

@section('styles')
<style>
    .feedback-reply-cell {
        min-width: 300px;
    }
    .feedback-reply-cell .fh-field {
        min-height: 34px;
        padding: .35rem .6rem;
        font-size: .85rem;
    }
    .feedback-reply-cell textarea {
        resize: vertical;
    }
</style>
@endsection
