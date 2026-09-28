@extends('layouts.admin')

@section('title', $formTitle.' · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">{{ $formTitle }}</h1>
            <p class="fh-page-sub">Launch dates, type, status and the title this release belongs to.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.releases.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to releases
        </a>
    </div>

    <div class="fh-card fh-card-pad" style="max-width: 900px;">
        @include('admin.partials.form-errors')

        <form
            method="POST"
            action="{{ $release->exists ? route('admin.releases.update', $release) : route('admin.releases.store') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @if($release->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <x-admin.form-row label="Title" for="title" required :error="$errors->first('title')">
                        <input class="fh-field" type="text" id="title" name="title" value="{{ old('title', $release->title) }}" maxlength="190" required>
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Release date" for="release_date" required :error="$errors->first('release_date')">
                        <input class="fh-field" type="date" id="release_date" name="release_date" value="{{ old('release_date', optional($release->release_date)->format('Y-m-d')) }}" required>
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <x-admin.select
                        name="release_type"
                        label="Type"
                        :options="collect($releaseTypes)->mapWithKeys(fn ($t) => [$t => $t])->all()"
                        :selected="old('release_type', $release->release_type ?? 'Season')"
                        required
                        :error="$errors->first('release_type')"
                    />
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.select
                        name="status"
                        label="Status"
                        :options="['upcoming' => 'Upcoming', 'released' => 'Released', 'delayed' => 'Delayed']"
                        :selected="old('status', $release->status ?? 'upcoming')"
                        required
                        :error="$errors->first('status')"
                    />
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Popularity (0–100)" for="popularity" :error="$errors->first('popularity')">
                        <input class="fh-field" type="number" id="popularity" name="popularity" value="{{ old('popularity', $release->popularity ?? 0) }}" min="0" max="100" inputmode="numeric">
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Category" for="category_id" :error="$errors->first('category_id')">
                        <select class="fh-field" id="category_id" name="category_id">
                            <option value="">No category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $release->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Linked content" for="content_id" :error="$errors->first('content_id')" hint="Optional — the title this release belongs to">
                        <select class="fh-field" id="content_id" name="content_id">
                            <option value="">Not linked</option>
                            @foreach($contents as $contentItem)
                                <option value="{{ $contentItem->id }}" @selected((int) old('content_id', $release->content_id) === $contentItem->id)>{{ $contentItem->title }}</option>
                            @endforeach
                        </select>
                    </x-admin.form-row>
                </div>
            </div>

            <x-admin.form-row label="Description" for="description" :error="$errors->first('description')">
                <textarea class="fh-field" id="description" name="description" rows="5" maxlength="3000">{{ old('description', $release->description) }}</textarea>
            </x-admin.form-row>

            <x-admin.form-row label="Poster" for="poster" :error="$errors->first('poster')" hint="jpg, png or webp — max 2 MB">
                <input class="fh-field" type="file" id="poster" name="poster" accept="image/jpeg,image/png,image/webp">
            </x-admin.form-row>

            @if($release->poster)
                <div class="mb-3">
                    <img src="{{ asset('storage/'.ltrim($release->poster, '/')) }}" alt="Current poster for {{ $release->title }}" width="90" height="135" loading="lazy" decoding="async" class="fh-img rounded" style="object-fit: cover;">
                    <p class="fh-help mb-0">Current poster — upload a new file to replace it.</p>
                </div>
            @endif

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-fh" type="submit">
                    <i class="bi bi-check-lg me-2" aria-hidden="true"></i>{{ $release->exists ? 'Save changes' : 'Create release' }}
                </button>
                <a class="btn btn-fh-ghost" href="{{ route('admin.releases.index') }}">Cancel</a>
            </div>
        </form>

        @if($release->exists)
            <div class="fh-divider my-4" aria-hidden="true"></div>

            <form
                method="POST"
                action="{{ route('admin.releases.destroy', $release) }}"
                data-confirm="Delete {{ $release->title }}? This cannot be undone."
            >
                @csrf
                @method('DELETE')
                <button class="btn btn-fh-danger" type="submit">
                    <i class="bi bi-trash me-2" aria-hidden="true"></i>Delete release
                </button>
            </form>
        @endif
    </div>
@endsection
