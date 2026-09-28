@extends('layouts.admin')

@section('title', $formTitle.' · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">{{ $formTitle }}</h1>
            <p class="fh-page-sub">Clip details, links, artwork and the gallery for this entry.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.media.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to media
        </a>
    </div>

    <div class="fh-card fh-card-pad" style="max-width: 960px;">
        @include('admin.partials.form-errors')

        <form
            method="POST"
            action="{{ $media->exists ? route('admin.media.update', $media) : route('admin.media.store') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @if($media->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <x-admin.form-row label="Title" for="title" required :error="$errors->first('title')">
                        <input class="fh-field" type="text" id="title" name="title" value="{{ old('title', $media->title) }}" maxlength="190" required>
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Category" for="category_id" required :error="$errors->first('category_id')">
                        <select class="fh-field" id="category_id" name="category_id" required>
                            <option value="">Choose…</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $media->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <x-admin.select
                        name="media_type"
                        label="Media type"
                        :options="['video' => 'Video', 'trailer' => 'Trailer', 'audio' => 'Audio', 'gallery' => 'Gallery']"
                        :selected="old('media_type', $media->media_type ?? 'video')"
                        required
                        :error="$errors->first('media_type')"
                    />
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.select
                        name="status"
                        label="Status"
                        :options="['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']"
                        :selected="old('status', $media->status ?? 'published')"
                        required
                        :error="$errors->first('status')"
                    />
                </div>

                <div class="col-6 col-md-2">
                    <x-admin.form-row label="Rating (0–10)" for="rating" :error="$errors->first('rating')">
                        <input class="fh-field" type="number" id="rating" name="rating" value="{{ old('rating', $media->rating ?? 0) }}" min="0" max="10" step="0.1" inputmode="decimal">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-2">
                    <x-admin.form-row label="Duration" for="duration" :error="$errors->first('duration')" hint="e.g. 12:40">
                        <input class="fh-field" type="text" id="duration" name="duration" value="{{ old('duration', $media->duration) }}" maxlength="20" placeholder="12:40">
                    </x-admin.form-row>
                </div>
            </div>

            <x-admin.form-row label="Description" for="description" :error="$errors->first('description')">
                <textarea class="fh-field" id="description" name="description" rows="5" maxlength="3000">{{ old('description', $media->description) }}</textarea>
            </x-admin.form-row>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Source URL" for="url" :error="$errors->first('url')" hint="Where this media plays">
                        <input class="fh-field" type="url" id="url" name="url" value="{{ old('url', $media->url) }}" maxlength="500" placeholder="https://…">
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Embed URL" for="embed_url" :error="$errors->first('embed_url')" hint="iframe-friendly link">
                        <input class="fh-field" type="url" id="embed_url" name="embed_url" value="{{ old('embed_url', $media->embed_url) }}" maxlength="500" placeholder="https://…">
                    </x-admin.form-row>
                </div>
            </div>

            @php
                $storedVideo = is_string($media->url) && str_starts_with($media->url, 'media/videos/');
            @endphp

            <x-admin.form-row label="Video file" for="video" :error="$errors->first('video')" hint="mp4, webm or mov — max 50 MB">
                <input class="fh-field" type="file" id="video" name="video" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov">
                <p class="fh-help mb-0 mt-2">Upload a file to play it on the media page. Leave this empty to keep using the links above.</p>
            </x-admin.form-row>

            @if($storedVideo)
                <div class="mb-3">
                    <p class="fh-help mb-2">Current video: {{ basename($media->url) }}</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remove_video" value="1" id="remove-video">
                        <label class="form-check-label fh-help" for="remove-video">Remove uploaded video</label>
                    </div>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Thumbnail" for="thumbnail" :error="$errors->first('thumbnail')" hint="jpg, png or webp — max 2 MB">
                        <input class="fh-field" type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/webp">
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Gallery images" for="gallery" :error="$errors->first('gallery')" hint="Multiple files, ≤ 2 MB each">
                        <input class="fh-field" type="file" id="gallery" name="gallery[]" accept="image/jpeg,image/png,image/webp" multiple>
                    </x-admin.form-row>
                </div>
            </div>

            <x-admin.form-row label="Featured" for="featured" help="Promoted on the home page">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="featured" name="featured" value="1" @checked(old('featured', $media->featured))>
                    <label class="form-check-label fh-help" for="featured">Featured media</label>
                </div>
            </x-admin.form-row>

            @if($media->thumbnail)
                <div class="mb-3">
                    <img src="{{ asset('storage/'.ltrim($media->thumbnail, '/')) }}" alt="Current thumbnail for {{ $media->title }}" width="160" height="90" loading="lazy" decoding="async" class="fh-img rounded" style="object-fit: cover;">
                    <p class="fh-help mb-0">Current thumbnail — upload a new file to replace it.</p>
                </div>
            @endif

            @if($media->exists && count((array) $media->gallery))
                <div class="mb-3">
                    <span class="fh-label d-block mb-2">Current gallery</span>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach((array) $media->gallery as $galleryPath)
                            <div class="text-center">
                                <img src="{{ asset('storage/'.ltrim($galleryPath, '/')) }}" alt="Gallery image {{ $loop->iteration }} of {{ $media->title }}" width="84" height="84" loading="lazy" decoding="async" class="fh-img rounded" style="object-fit: cover;">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="remove_gallery[]" value="{{ $galleryPath }}" id="remove-media-gallery-{{ $loop->index }}">
                                    <label class="form-check-label fh-help" for="remove-media-gallery-{{ $loop->index }}">Remove</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @foreach((array) $media->gallery as $galleryPath)
                    <input type="hidden" name="_current_gallery[]" value="{{ $galleryPath }}">
                @endforeach
            @endif

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-fh" type="submit">
                    <i class="bi bi-check-lg me-2" aria-hidden="true"></i>{{ $media->exists ? 'Save changes' : 'Create media' }}
                </button>
                <a class="btn btn-fh-ghost" href="{{ route('admin.media.index') }}">Cancel</a>
            </div>
        </form>

        @if($media->exists)
            <div class="fh-divider my-4" aria-hidden="true"></div>

            <form
                method="POST"
                action="{{ route('admin.media.destroy', $media) }}"
                data-confirm="Delete {{ $media->title }}? This cannot be undone."
            >
                @csrf
                @method('DELETE')
                <button class="btn btn-fh-danger" type="submit">
                    <i class="bi bi-trash me-2" aria-hidden="true"></i>Delete media
                </button>
            </form>
        @endif
    </div>
@endsection
