@extends('layouts.admin')

@section('title', $formTitle.' · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">{{ $formTitle }}</h1>
            <p class="fh-page-sub">HTML body, cover art, byline and publishing state.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.articles.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to articles
        </a>
    </div>

    <div class="fh-card fh-card-pad" style="max-width: 960px;">
        @include('admin.partials.form-errors')

        <form
            method="POST"
            action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @if($article->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <x-admin.form-row label="Title" for="title" required :error="$errors->first('title')">
                        <input class="fh-field" type="text" id="title" name="title" value="{{ old('title', $article->title) }}" maxlength="190" required>
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Category" for="category_id" required :error="$errors->first('category_id')">
                        <select class="fh-field" id="category_id" name="category_id" required>
                            <option value="">Choose…</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $article->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Author" for="author_id" required :error="$errors->first('author_id')" help="Defaults to you">
                        <select class="fh-field" id="author_id" name="author_id" required>
                            <option value="">Choose…</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}" @selected((int) old('author_id', $article->author_id) === $author->id)>
                                    {{ $author->name }} ({{ $author->email }})
                                </option>
                            @endforeach
                        </select>
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Status" for="status" required :error="$errors->first('status')">
                        <select class="fh-field" id="status" name="status" required>
                            <option value="draft" @selected(old('status', $article->status ?? 'published') === 'draft')>Draft</option>
                            <option value="published" @selected(old('status', $article->status ?? 'published') === 'published')>Published</option>
                            <option value="archived" @selected(old('status', $article->status ?? 'published') === 'archived')>Archived</option>
                        </select>
                    </x-admin.form-row>
                </div>
            </div>

            <x-admin.form-row label="Excerpt" for="excerpt" :error="$errors->first('excerpt')" hint="Shown on cards and in listings">
                <textarea class="fh-field" id="excerpt" name="excerpt" rows="2" maxlength="500">{{ old('excerpt', $article->excerpt) }}</textarea>
            </x-admin.form-row>

            <x-admin.form-row
                label="Body (HTML)"
                for="body"
                required
                :error="$errors->first('body')"
                help="Rendered unescaped on the public site — only paste HTML you trust."
            >
                <textarea class="fh-field" id="body" name="body" rows="16" maxlength="200000" required>{{ old('body', $article->body) }}</textarea>
            </x-admin.form-row>

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Reading time (min)" for="reading_time" :error="$errors->first('reading_time')" help="Auto from word count when blank">
                        <input class="fh-field" type="number" id="reading_time" name="reading_time" value="{{ old('reading_time', $article->reading_time) }}" min="1" max="240" inputmode="numeric" placeholder="Auto">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Published at" for="published_at" :error="$errors->first('published_at')">
                        <input class="fh-field" type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', optional($article->published_at)->format('Y-m-d\TH:i')) }}">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Featured" for="featured" help="Promoted on the home page">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="featured" name="featured" value="1" @checked(old('featured', $article->featured))>
                            <label class="form-check-label fh-help" for="featured">Featured article</label>
                        </div>
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Cover image" for="cover_image" :error="$errors->first('cover_image')" hint="jpg/png/webp ≤ 2 MB">
                        <input class="fh-field" type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp">
                    </x-admin.form-row>
                </div>
            </div>

            @if($article->cover_image)
                <div class="mb-3">
                    <img src="{{ asset('storage/'.ltrim($article->cover_image, '/')) }}" alt="Current cover for {{ $article->title }}" width="160" height="90" loading="lazy" decoding="async" class="fh-img rounded" style="object-fit: cover;">
                    <p class="fh-help mb-0">Current cover — upload a new file to replace it.</p>
                </div>
            @endif

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-fh" type="submit">
                    <i class="bi bi-check-lg me-2" aria-hidden="true"></i>{{ $article->exists ? 'Save changes' : 'Create article' }}
                </button>
                <a class="btn btn-fh-ghost" href="{{ route('admin.articles.index') }}">Cancel</a>
            </div>
        </form>

        @if($article->exists)
            <div class="fh-divider my-4" aria-hidden="true"></div>

            <form
                method="POST"
                action="{{ route('admin.articles.destroy', $article) }}"
                data-confirm="Delete {{ $article->title }}? This cannot be undone."
            >
                @csrf
                @method('DELETE')
                <button class="btn btn-fh-danger" type="submit">
                    <i class="bi bi-trash me-2" aria-hidden="true"></i>Delete article
                </button>
            </form>
        @endif
    </div>
@endsection
