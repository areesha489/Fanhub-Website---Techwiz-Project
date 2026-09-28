@extends('layouts.admin')

@section('title', 'Chatbot knowledge · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Chatbot knowledge</h1>
            <p class="fh-page-sub">Question and answer pairs the help assistant replies from.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-fh-outline" href="{{ route('admin.chatbot.queries') }}">
                <i class="bi bi-clock-history me-2" aria-hidden="true"></i>Query log
            </a>
            <a class="btn btn-fh" href="{{ route('admin.chatbot.create') }}">
                <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add entry
            </a>
        </div>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.chatbot.index'),
            'searchPlaceholder' => 'Search question, answer or keywords…',
            'filters' => [
                ['name' => 'active', 'label' => 'Availability', 'value' => $active, 'options' => ['1' => 'Active only', '0' => 'Inactive only']],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $knowledgeColumns = [
                    ['key' => 'question', 'label' => 'Question', 'format' => function ($row) {
                        $link = e(route('admin.chatbot.edit', $row));

                        return '<a href="'.$link.'" class="fh-table-title">'.e($row->question).'</a>'
                            .'<div class="small text-muted">'.e(\Illuminate\Support\Str::limit((string) $row->answer, 80)).'</div>';
                    }],
                    ['key' => 'category', 'label' => 'Category', 'format' => fn ($row) => $row->category ? '<span class="fh-chip">'.e($row->category).'</span>' : '<span class="text-muted">—</span>'],
                    ['key' => 'keywords', 'label' => 'Keywords', 'format' => fn ($row) => $row->keywords ? e(\Illuminate\Support\Str::limit((string) $row->keywords, 55)) : '<span class="text-muted">—</span>'],
                    ['key' => 'is_active', 'label' => 'Available', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->is_active])->render()],
                    ['key' => 'updated_at', 'label' => 'Updated', 'format' => fn ($row) => e($row->updated_at?->format('d M Y'))],
                    ['key' => null, 'label' => 'Actions', 'class' => 'text-end', 'format' => fn ($row) => view('admin.partials.row-actions', [
                        'editRoute' => route('admin.chatbot.edit', $row),
                        'deleteRoute' => route('admin.chatbot.destroy', $row),
                        'label' => $row->question,
                        'confirm' => 'Delete this knowledge entry? The chatbot stops answering with it.',
                    ])->render()],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$knowledgeColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No knowledge entries"
                message="Teach the chatbot its first question and answer."
                icon="bi-bot"
            >
                <a class="btn btn-fh" href="{{ route('admin.chatbot.create') }}">Add entry</a>
            </x-empty>
        @endif
    </div>
@endsection
