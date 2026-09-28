@extends('layouts.admin')

@section('title', $formTitle.' · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">{{ $formTitle }}</h1>
            <p class="fh-page-sub">Metadata, artwork, tags and cast for a catalogue title.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.content.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to content
        </a>
    </div>

    <div class="fh-card fh-card-pad" style="max-width: 980px;">
        @include('admin.partials.form-errors')

        <form
            method="POST"
            action="{{ $content->exists ? route('admin.content.update', $content) : route('admin.content.store') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @if($content->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <x-admin.form-row label="Title" for="title" required :error="$errors->first('title')">
                        <input class="fh-field" type="text" id="title" name="title" value="{{ old('title', $content->title) }}" maxlength="190" required>
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Genre" for="genre" :error="$errors->first('genre')">
                        <input class="fh-field" type="text" id="genre" name="genre" value="{{ old('genre', $content->genre) }}" maxlength="120" placeholder="Shonen, Roguelike…">
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <x-admin.select
                        name="category_id"
                        label="Category"
                        :options="$categories->mapWithKeys(fn ($c) => [$c->id => $c->name])->all()"
                        :selected="old('category_id', $content->category_id)"
                        placeholder="Choose a category…"
                        required
                        :error="$errors->first('category_id')"
                    />
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.select
                        name="content_type"
                        label="Content type"
                        :options="collect(\App\Models\Content::TYPES)->mapWithKeys(fn ($t) => [$t => $t])->all()"
                        :selected="old('content_type', $content->content_type ?? 'Series')"
                        required
                        :error="$errors->first('content_type')"
                    />
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.select
                        name="status"
                        label="Status"
                        :options="['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']"
                        :selected="old('status', $content->status ?? 'published')"
                        required
                        :error="$errors->first('status')"
                    />
                </div>
            </div>

            <x-admin.form-row label="Description" for="description" :error="$errors->first('description')" hint="Short teaser shown on cards">
                <textarea class="fh-field" id="description" name="description" rows="3" maxlength="2000">{{ old('description', $content->description) }}</textarea>
            </x-admin.form-row>

            <x-admin.form-row label="Synopsis" for="synopsis" :error="$errors->first('synopsis')" hint="Long-form summary shown on the detail page">
                <textarea class="fh-field" id="synopsis" name="synopsis" rows="6" maxlength="6000">{{ old('synopsis', $content->synopsis) }}</textarea>
            </x-admin.form-row>

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Release date" for="release_date" :error="$errors->first('release_date')">
                        <input class="fh-field" type="date" id="release_date" name="release_date" value="{{ old('release_date', optional($content->release_date)->format('Y-m-d')) }}">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Rating (0–10)" for="rating" :error="$errors->first('rating')">
                        <input class="fh-field" type="number" id="rating" name="rating" value="{{ old('rating', $content->rating ?? 0) }}" min="0" max="10" step="0.1" inputmode="decimal">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Popularity (0–100)" for="popularity" :error="$errors->first('popularity')">
                        <input class="fh-field" type="number" id="popularity" name="popularity" value="{{ old('popularity', $content->popularity ?? 0) }}" min="0" max="100" step="1" inputmode="numeric">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Featured" for="featured" help="Promoted on the home page">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="featured" name="featured" value="1" @checked(old('featured', $content->featured))>
                            <label class="form-check-label fh-help" for="featured">Show as featured</label>
                        </div>
                    </x-admin.form-row>
                </div>
            </div>

            <x-admin.form-row label="Trailer URL" for="trailer_url" :error="$errors->first('trailer_url')" hint="Full YouTube or Vimeo link">
                <input class="fh-field" type="url" id="trailer_url" name="trailer_url" value="{{ old('trailer_url', $content->trailer_url) }}" maxlength="500" placeholder="https://www.youtube.com/watch?v=…">
            </x-admin.form-row>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Cover image" for="cover_image" :error="$errors->first('cover_image')" hint="jpg, png or webp — max 2 MB">
                        <input class="fh-field" type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp">
                    </x-admin.form-row>

                    @if($content->cover_image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/'.ltrim($content->cover_image, '/')) }}" alt="Current cover for {{ $content->title }}" width="140" height="79" loading="lazy" decoding="async" class="fh-img rounded" style="object-fit: cover;">
                            <p class="fh-help mb-0">Current cover — upload a new file to replace it.</p>
                        </div>
                    @endif
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Banner image" for="banner_image" :error="$errors->first('banner_image')" hint="jpg, png or webp — max 2 MB">
                        <input class="fh-field" type="file" id="banner_image" name="banner_image" accept="image/jpeg,image/png,image/webp">
                    </x-admin.form-row>

                    @if($content->banner_image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/'.ltrim($content->banner_image, '/')) }}" alt="Current banner for {{ $content->title }}" width="140" height="79" loading="lazy" decoding="async" class="fh-img rounded" style="object-fit: cover;">
                            <p class="fh-help mb-0">Current banner — upload a new file to replace it.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="fh-divider mb-3" aria-hidden="true"></div>

            <h2 class="fh-card-title">Tags</h2>
            <p class="fh-help">Tick existing tags and/or type comma-separated new ones — they are created and linked automatically.</p>

            <x-admin.form-row label="Existing tags" for="tag-list" :error="$errors->first('tags')">
                <div id="tag-list" class="d-flex flex-wrap gap-2 p-2 rounded" style="background: rgba(255,255,255,.03); border: 1px solid var(--fh-border-2);">
                    @forelse($tags as $tag)
                        <div class="form-check me-2">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="tags[]"
                                value="{{ $tag->id }}"
                                id="tag-{{ $tag->id }}"
                                @checked(in_array($tag->id, collect(old('tags', $attachTags->pluck('id')->all()))->map(fn ($v) => (int) $v)->all()))
                            >
                            <label class="form-check-label fh-help" for="tag-{{ $tag->id }}">{{ $tag->name }}</label>
                        </div>
                    @empty
                        <span class="fh-help">No tags in the library yet — use the field below to create some.</span>
                    @endforelse
                </div>
            </x-admin.form-row>

            <x-admin.form-row label="New tags" for="new_tags" :error="$errors->first('new_tags')" hint="Comma separated">
                <input class="fh-field" type="text" id="new_tags" name="new_tags" value="{{ old('new_tags') }}" maxlength="300" placeholder="shonen, isekai, retro">
            </x-admin.form-row>

            <div class="fh-divider mb-3" aria-hidden="true"></div>

            <h2 class="fh-card-title">Characters</h2>
            <p class="fh-help">Tick the characters that appear in this title and optionally give each one a role.</p>

            <x-admin.form-row label="Cast" for="cast-list" :error="$errors->first('characters')">
                <div id="cast-list" class="p-2 rounded" style="background: rgba(255,255,255,.03); border: 1px solid var(--fh-border-2); max-height: 300px; overflow-y: auto;">
                    @forelse($characters as $character)
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="characters[]"
                                    value="{{ $character->id }}"
                                    id="character-{{ $character->id }}"
                                    @checked(in_array($character->id, collect(old('characters', $attachCharacters->pluck('id')->all()))->map(fn ($v) => (int) $v)->all()))
                                >
                                <label class="form-check-label fh-help" for="character-{{ $character->id }}">{{ $character->name }}</label>
                            </div>
                            <input
                                class="fh-field"
                                type="text"
                                name="character_roles[{{ $character->id }}]"
                                value="{{ old('character_roles.'.$character->id, $attachCharacters->firstWhere('id', $character->id)?->pivot?->role) }}"
                                maxlength="120"
                                placeholder="Role (optional)"
                                style="max-width: 240px; min-height: 34px; padding: .3rem .6rem;"
                                aria-label="Role for {{ $character->name }}"
                            >
                        </div>
                    @empty
                        <span class="fh-help">No characters in the database yet — add them under Characters first.</span>
                    @endforelse
                </div>
            </x-admin.form-row>

            <div class="d-flex gap-2 flex-wrap mt-3">
                <button class="btn btn-fh" type="submit">
                    <i class="bi bi-check-lg me-2" aria-hidden="true"></i>{{ $content->exists ? 'Save changes' : 'Create content' }}
                </button>
                <a class="btn btn-fh-ghost" href="{{ route('admin.content.index') }}">Cancel</a>
            </div>
        </form>

        @if($content->exists)
            <div class="fh-divider my-4" aria-hidden="true"></div>

            <form
                method="POST"
                action="{{ route('admin.content.destroy', $content) }}"
                data-confirm="Delete {{ $content->title }}? This cannot be undone."
            >
                @csrf
                @method('DELETE')
                <button class="btn btn-fh-danger" type="submit">
                    <i class="bi bi-trash me-2" aria-hidden="true"></i>Delete content
                </button>
            </form>
        @endif
    </div>
@endsection
