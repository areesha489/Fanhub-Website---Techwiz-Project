@extends('layouts.admin')

@section('title', 'Media · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Multimedia</h1>
            <p class="fh-page-sub">Videos, trailers, audio clips and galleries for the fandoms you cover.</p>
        </div>
        <a class="btn btn-fh" href="{{ route('admin.media.create') }}">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add media
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.media.index'),
            'searchPlaceholder' => 'Search title or description…',
            'filters' => [
                ['name' => 'type', 'label' => 'Type', 'value' => $type, 'options' => ['video' => 'Video', 'trailer' => 'Trailer', 'audio' => 'Audio', 'gallery' => 'Gallery']],
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div class="fh-card fh-card-pad">
        @if($items->count())
            @php
                $mediaColumns = [
                    ['key' => 'title', 'label' => 'Title', 'format' => function ($row) {
                        $link = e(route('admin.media.edit', $row));

                        return '<a href="'.$link.'" class="fh-table-title">'.e($row->title).'</a>'
                            .'<div class="small text-muted">'.e($row->duration ?: $row->url ?: 'No link').'</div>';
                    }],
                    ['key' => 'category.name', 'label' => 'Category'],
                    ['key' => 'media_type', 'label' => 'Type', 'format' => fn ($row) => '<span class="fh-chip">'.e(ucfirst($row->media_type)).'</span>'],
                    ['key' => 'status', 'label' => 'Status', 'format' => fn ($row) => view('components.admin.status-badge', ['value' => $row->status])->render()],
                    ['key' => 'rating', 'label' => 'Rating', 'format' => fn ($row) => e(number_format((float) $row->rating, 1))],
                    ['key' => 'views_count', 'label' => 'Views', 'format' => fn ($row) => number_format((int) $row->views_count)],
                    ['key' => 'featured', 'label' => 'Featured', 'format' => fn ($row) => $row->featured ? '<i class="bi bi-star-fill text-warning" aria-label="Featured"></i>' : '<span class="text-muted">—</span>'],
                    ['key' => null, 'label' => 'Actions', 'class' => 'text-end', 'format' => fn ($row) => view('admin.partials.row-actions', [
                        'editRoute' => route('admin.media.edit', $row),
                        'deleteRoute' => route('admin.media.destroy', $row),
                        'label' => $row->title,
                        'confirm' => 'Delete "'.$row->title.'"? This cannot be undone.',
                    ])->render()],
                ];
            @endphp

            <x-admin.table :items="$items" :columns="$mediaColumns" />

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No media yet"
                message="Add the first video, trailer, audio clip or gallery."
                icon="bi-play-circle"
            >
                <a class="btn btn-fh" href="{{ route('admin.media.create') }}">Add media</a>
            </x-empty>
        @endif
    </div>
@endsection
