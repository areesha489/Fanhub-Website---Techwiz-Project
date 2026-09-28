@extends('layouts.admin')

@section('title', $formTitle.' · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">{{ $formTitle }}</h1>
            <p class="fh-page-sub">Product details, pricing and artwork for the merch shelf.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.merchandise.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to merchandise
        </a>
    </div>

    <div class="fh-card fh-card-pad" style="max-width: 900px;">
        @include('admin.partials.form-errors')

        <form
            method="POST"
            action="{{ $item->exists ? route('admin.merchandise.update', $item) : route('admin.merchandise.store') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @if($item->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <x-admin.form-row label="Title" for="title" required :error="$errors->first('title')">
                        <input class="fh-field" type="text" id="title" name="title" value="{{ old('title', $item->title) }}" maxlength="190" required>
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Category" for="category_id" required :error="$errors->first('category_id')">
                        <select class="fh-field" id="category_id" name="category_id" required>
                            <option value="">Choose…</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $item->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Fandom" for="fandom" :error="$errors->first('fandom')">
                        <input class="fh-field" type="text" id="fandom" name="fandom" value="{{ old('fandom', $item->fandom) }}" maxlength="150" placeholder="One Piece…">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Price" for="price" :error="$errors->first('price')">
                        <input class="fh-field" type="number" id="price" name="price" value="{{ old('price', $item->price) }}" min="0" max="999999.99" step="0.01" inputmode="decimal" placeholder="49.99">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Currency" for="currency" :error="$errors->first('currency')" hint="3-letter code">
                        <input class="fh-field" type="text" id="currency" name="currency" value="{{ old('currency', $item->currency ?? 'USD') }}" maxlength="3" minlength="3" style="text-transform: uppercase;">
                    </x-admin.form-row>
                </div>
            </div>

            <x-admin.form-row label="Description" for="description" :error="$errors->first('description')">
                <textarea class="fh-field" id="description" name="description" rows="5" maxlength="3000">{{ old('description', $item->description) }}</textarea>
            </x-admin.form-row>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Tags" for="tags" :error="$errors->first('tags')" hint="Comma separated">
                        <input class="fh-field" type="text" id="tags" name="tags" value="{{ old('tags', is_array($item->tags) ? implode(', ', $item->tags) : $item->tags) }}" maxlength="300" placeholder="figure, limited, vinyl">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Release date" for="release_date" :error="$errors->first('release_date')">
                        <input class="fh-field" type="date" id="release_date" name="release_date" value="{{ old('release_date', optional($item->release_date)->format('Y-m-d')) }}">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Popularity (0–100)" for="popularity" :error="$errors->first('popularity')">
                        <input class="fh-field" type="number" id="popularity" name="popularity" value="{{ old('popularity', $item->popularity ?? 0) }}" min="0" max="100" inputmode="numeric">
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <x-admin.select
                        name="status"
                        label="Status"
                        :options="['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']"
                        :selected="old('status', $item->status ?? 'published')"
                        required
                        :error="$errors->first('status')"
                    />
                </div>

                <div class="col-6 col-md-4">
                    <x-admin.form-row label="Upcoming" for="is_upcoming" help="Marks a pre-release product">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_upcoming" name="is_upcoming" value="1" @checked(old('is_upcoming', $item->is_upcoming))>
                            <label class="form-check-label fh-help" for="is_upcoming">Not released yet</label>
                        </div>
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-4">
                    <x-admin.form-row label="Product image" for="image" :error="$errors->first('image')" hint="jpg, png or webp — max 2 MB">
                        <input class="fh-field" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    </x-admin.form-row>
                </div>
            </div>

            @if($item->image)
                <div class="mb-3">
                    <img src="{{ asset('storage/'.ltrim($item->image, '/')) }}" alt="Current image for {{ $item->title }}" width="90" height="90" loading="lazy" decoding="async" class="fh-img rounded" style="object-fit: cover;">
                    <p class="fh-help mb-0">Current image — upload a new file to replace it.</p>
                </div>
            @endif

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-fh" type="submit">
                    <i class="bi bi-check-lg me-2" aria-hidden="true"></i>{{ $item->exists ? 'Save changes' : 'Create item' }}
                </button>
                <a class="btn btn-fh-ghost" href="{{ route('admin.merchandise.index') }}">Cancel</a>
            </div>
        </form>

        @if($item->exists)
            <div class="fh-divider my-4" aria-hidden="true"></div>

            <form
                method="POST"
                action="{{ route('admin.merchandise.destroy', $item) }}"
                data-confirm="Delete {{ $item->title }}? This cannot be undone."
            >
                @csrf
                @method('DELETE')
                <button class="btn btn-fh-danger" type="submit">
                    <i class="bi bi-trash me-2" aria-hidden="true"></i>Delete item
                </button>
            </form>
        @endif
    </div>
@endsection
