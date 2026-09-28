@extends('layouts.admin')

@section('title', 'Content · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Content</h1>
            <p class="fh-page-sub">Series, movies, games, albums and chapters across the whole catalogue.</p>
        </div>
        <a class="btn btn-fh" href="{{ route('admin.content.create') }}">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add content
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.content.index'),
            'searchPlaceholder' => 'Search title, genre or description…',
            'filters' => [
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']],
                ['name' => 'type', 'label' => 'Type', 'value' => $type, 'options' => collect(\App\Models\Content::TYPES)->mapWithKeys(fn ($t) => [$t => $t])->all()],
                ['name' => 'category', 'label' => 'Category', 'value' => $categoryId, 'options' => $categories->mapWithKeys(fn ($c) => [$c->id => $c->name])->all()],
            ],
            'sortOptions' => $sortOptions,
            'sortValue' => $sort,
        ])
    </div>

    <div>
        @if($items->count())
            <div class="fh-admin-cards">
                @foreach($items as $row)
                    <article class="fh-admin-card">
                        <a class="fh-admin-card-media fh-admin-card-media-wide" href="{{ route('admin.content.edit', $row) }}">
                            <img src="{{ img($row->cover_image, $row->title, 480, 300, $row->category?->slug) }}" alt="" width="480" height="300" loading="lazy" decoding="async">
                        </a>
                        <div class="fh-admin-card-body">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <x-admin.status-badge :value="$row->status" />
                                @if($row->featured)
                                    <i class="bi bi-star-fill text-warning" aria-label="Featured"></i>
                                @endif
                            </div>
                            <h2 class="fh-admin-card-title">
                                <a href="{{ route('admin.content.edit', $row) }}">{{ $row->title }}</a>
                            </h2>
                            <p class="fh-admin-card-meta">{{ $row->category?->name ?: 'No category' }} · {{ $row->content_type }}</p>
                            <p class="fh-admin-card-meta"><i class="bi bi-eye" aria-hidden="true"></i> {{ number_format((int) $row->views_count) }}</p>
                            @include('admin.partials.row-actions', [
                                'editRoute' => route('admin.content.edit', $row),
                                'deleteRoute' => route('admin.content.destroy', $row),
                                'label' => $row->title,
                                'confirm' => 'Delete "'.$row->title.'"? This cannot be undone.',
                            ])
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <x-admin.pagination-head :paginator="$items" />
                <x-pagination :paginator="$items" />
            </div>
        @else
            <x-empty
                title="No content matches"
                message="Adjust the filters, or add the first title to the catalogue."
                icon="bi-film"
            >
                <a class="btn btn-fh" href="{{ route('admin.content.create') }}">Add content</a>
            </x-empty>
        @endif
    </div>
@endsection
