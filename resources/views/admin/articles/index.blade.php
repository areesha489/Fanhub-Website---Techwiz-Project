@extends('layouts.admin')

@section('title', 'Articles · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Articles</h1>
            <p class="fh-page-sub">Editorial pieces, guides and op-eds written by the team and members.</p>
        </div>
        <a class="btn btn-fh" href="{{ route('admin.articles.create') }}">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Write article
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.articles.index'),
            'searchPlaceholder' => 'Search title or excerpt…',
            'filters' => [
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $articleColumns = [
                    ['key' => 'title', 'label' => 'Title', 'format' => function ($row) {
                        $link = e(route('admin.articles.edit', $row));

                        return '<a href="'.$link.'" class="fh-table-title">'.e($row->title).'</a>'
                            .'<div class="small text-muted">'.e(\Illuminate\Support\Str::limit((string) $row->excerpt, 70)).'</div>';
                    }],
                    ['key' => 'category.name', 'label' => 'Category'],
                    ['key' => 'author.name', 'label' => 'Author'],
                    ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                    ['key' => 'reading_time', 'label' => 'Read', 'format' => fn ($row) => number_format((int) $row->reading_time).' min'],
                    ['key' => 'views_count', 'label' => 'Views', 'format' => fn ($row) => number_format((int) $row->views_count)],
                    ['key' => 'published_at', 'label' => 'Published', 'format' => fn ($row) => e($row->published_at?->format('d M Y') ?? '—')],
                    ['key' => null, 'label' => 'Actions', 'class' => 'text-end', 'format' => fn ($row) => view('admin.partials.row-actions', [
                        'editRoute' => route('admin.articles.edit', $row),
                        'deleteRoute' => route('admin.articles.destroy', $row),
                        'label' => $row->title,
                        'confirm' => 'Delete "'.$row->title.'"? This cannot be undone.',
                    ])->render()],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$articleColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No articles yet"
                message="Draft the first article for the editorial section."
                icon="bi-newspaper"
            >
                <a class="btn btn-fh" href="{{ route('admin.articles.create') }}">Write article</a>
            </x-empty>
        @endif
    </div>
@endsection
