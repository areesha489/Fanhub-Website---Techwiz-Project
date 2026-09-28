@extends('layouts.admin')

@section('title', 'Characters · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Characters</h1>
            <p class="fh-page-sub">Cast members, their fandoms and how they relate to each other.</p>
        </div>
        <a class="btn btn-fh" href="{{ route('admin.characters.create') }}">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add character
        </a>
    </div>

    <div class="fh-card fh-card-pad mb-4">
        @include('admin.partials.filter-bar', [
            'action' => route('admin.characters.index'),
            'searchPlaceholder' => 'Search name, fandom or bio…',
            'filters' => [
                ['name' => 'category', 'label' => 'Category', 'value' => $categoryId, 'options' => $categories->mapWithKeys(fn ($c) => [$c->id => $c->name])->all()],
                ['name' => 'featured', 'label' => 'Featured', 'value' => $featured, 'options' => ['1' => 'Featured only', '0' => 'Not featured']],
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
                        <a class="fh-admin-card-media" href="{{ route('admin.characters.edit', $row) }}">
                            <img src="{{ img($row->image, $row->name, 360, 480, $row->category?->slug) }}" alt="" width="360" height="480" loading="lazy" decoding="async">
                        </a>
                        <div class="fh-admin-card-body">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <span class="fh-chip">{{ $row->category?->name ?: 'No category' }}</span>
                                @if($row->featured)
                                    <i class="bi bi-star-fill text-warning" aria-label="Featured"></i>
                                @endif
                            </div>
                            <h2 class="fh-admin-card-title">
                                <a href="{{ route('admin.characters.edit', $row) }}">{{ $row->name }}</a>
                            </h2>
                            <p class="fh-admin-card-meta">{{ $row->fandom ?: 'No fandom' }}</p>
                            <p class="fh-admin-card-meta"><i class="bi bi-eye" aria-hidden="true"></i> {{ number_format((int) $row->views_count) }}</p>
                            @include('admin.partials.row-actions', [
                                'editRoute' => route('admin.characters.edit', $row),
                                'deleteRoute' => route('admin.characters.destroy', $row),
                                'label' => $row->name,
                                'confirm' => 'Delete '.$row->name.'? Relationships and gallery images are removed too.',
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
                title="No characters yet"
                message="Add the first character to start building the cast."
                icon="bi-people"
            >
                <a class="btn btn-fh" href="{{ route('admin.characters.create') }}">Add character</a>
            </x-empty>
        @endif
    </div>
@endsection
