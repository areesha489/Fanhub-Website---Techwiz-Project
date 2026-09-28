@extends('layouts.admin')

@section('title', 'Chatbot queries · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Chatbot query log</h1>
            <p class="fh-page-sub">Every question asked of the assistant and the reply it gave.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.chatbot.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to knowledge
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.chatbot.queries'),
            'searchPlaceholder' => 'Search question or response…',
            'filters' => [
                ['name' => 'user', 'label' => 'Asked by', 'value' => $hasUser, 'options' => ['1' => 'Members only', '0' => 'Guests only']],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $queryColumns = [
                    ['key' => 'message', 'label' => 'Asked', 'format' => fn ($row) => '<div>'.e(\Illuminate\Support\Str::limit((string) $row->message, 90)).'</div><div class="small text-muted">'.e($row->created_at?->format('d M Y H:i')).'</div>'],
                    ['key' => 'response', 'label' => 'Response', 'format' => fn ($row) => e(\Illuminate\Support\Str::limit((string) $row->response, 110))],
                    ['key' => null, 'label' => 'Asked by', 'format' => function ($row) {
                        if ($row->user) {
                            return '<div>'.e($row->user->name).'</div><div class="small text-muted">'.e($row->user->email).'</div>';
                        }

                        return '<span class="fh-chip">Guest</span>';
                    }],
                    ['key' => 'knowledge.question', 'label' => 'Matched entry', 'format' => function ($row) {
                        if (! $row->knowledge) {
                            return '<span class="text-muted">No match</span>';
                        }

                        return '<a href="'.e(route('admin.chatbot.edit', $row->knowledge)).'">'.e(\Illuminate\Support\Str::limit($row->knowledge->question, 45)).'</a>';
                    }],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$queryColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No queries logged"
                message="Conversations with the assistant will appear here."
                icon="bi-chat-left-text"
            />
        @endif
    </div>
@endsection
