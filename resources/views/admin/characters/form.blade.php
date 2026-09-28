@extends('layouts.admin')

@section('title', $formTitle.' · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">{{ $formTitle }}</h1>
            <p class="fh-page-sub">Profile, artwork, gallery and relationships for this character.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.characters.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to characters
        </a>
    </div>

    @if($errors->any())
        <div class="mb-3">@include('admin.partials.form-errors')</div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="fh-card fh-card-pad">
                <form
                    method="POST"
                    action="{{ $character->exists ? route('admin.characters.update', $character) : route('admin.characters.store') }}"
                    enctype="multipart/form-data"
                >
                    @csrf
                    @if($character->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <x-admin.form-row label="Name" for="name" required :error="$errors->first('name')">
                                <input class="fh-field" type="text" id="name" name="name" value="{{ old('name', $character->name) }}" maxlength="150" required>
                            </x-admin.form-row>
                        </div>

                        <div class="col-12 col-md-4">
                            <x-admin.form-row label="Category" for="category_id" required :error="$errors->first('category_id')">
                                <select class="fh-field" id="category_id" name="category_id" required>
                                    <option value="">Choose…</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) old('category_id', $character->category_id) === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </x-admin.form-row>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <x-admin.form-row label="Fandom" for="fandom" :error="$errors->first('fandom')">
                                <input class="fh-field" type="text" id="fandom" name="fandom" value="{{ old('fandom', $character->fandom) }}" maxlength="150" placeholder="Attack on Titan…">
                            </x-admin.form-row>
                        </div>

                        <div class="col-6 col-md-3">
                            <x-admin.form-row label="Gender" for="gender" :error="$errors->first('gender')">
                                <input class="fh-field" type="text" id="gender" name="gender" value="{{ old('gender', $character->gender) }}" maxlength="40">
                            </x-admin.form-row>
                        </div>

                        <div class="col-6 col-md-3">
                            <x-admin.form-row label="Age" for="age" :error="$errors->first('age')">
                                <input class="fh-field" type="text" id="age" name="age" value="{{ old('age', $character->age) }}" maxlength="60" placeholder="19 / Unknown">
                            </x-admin.form-row>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <x-admin.form-row label="Affiliation" for="affiliation" :error="$errors->first('affiliation')">
                                <input class="fh-field" type="text" id="affiliation" name="affiliation" value="{{ old('affiliation', $character->affiliation) }}" maxlength="150" placeholder="Scout Regiment…">
                            </x-admin.form-row>
                        </div>

                        <div class="col-12 col-md-6">
                            <x-admin.form-row label="Debut" for="debut" :error="$errors->first('debut')" hint="Chapter, episode or year">
                                <input class="fh-field" type="text" id="debut" name="debut" value="{{ old('debut', $character->debut) }}" maxlength="120" placeholder="Chapter 1 · 2009">
                            </x-admin.form-row>
                        </div>
                    </div>

                    <x-admin.form-row label="Abilities" for="abilities" :error="$errors->first('abilities')" hint="Comma separated highlights">
                        <input class="fh-field" type="text" id="abilities" name="abilities" value="{{ old('abilities', $character->abilities) }}" maxlength="600" placeholder="ODM gear, Titan shifting…">
                    </x-admin.form-row>

                    <x-admin.form-row label="Biography" for="bio" :error="$errors->first('bio')">
                        <textarea class="fh-field" id="bio" name="bio" rows="6" maxlength="4000">{{ old('bio', $character->bio) }}</textarea>
                    </x-admin.form-row>

                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <x-admin.form-row label="Popularity (0–100)" for="popularity" :error="$errors->first('popularity')">
                                <input class="fh-field" type="number" id="popularity" name="popularity" value="{{ old('popularity', $character->popularity ?? 0) }}" min="0" max="100" inputmode="numeric">
                            </x-admin.form-row>
                        </div>

                        <div class="col-6 col-md-3">
                            <x-admin.form-row label="Featured" for="featured" help="Shown on the home page">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="featured" name="featured" value="1" @checked(old('featured', $character->featured))>
                                    <label class="form-check-label fh-help" for="featured">Featured character</label>
                                </div>
                            </x-admin.form-row>
                        </div>

                        <div class="col-12 col-md-6">
                            <x-admin.form-row label="Portrait" for="image" :error="$errors->first('image')" hint="jpg, png or webp — max 2 MB">
                                <input class="fh-field" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                            </x-admin.form-row>
                        </div>
                    </div>

                    @if($character->image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/'.ltrim($character->image, '/')) }}" alt="Current portrait of {{ $character->name }}" width="90" height="90" loading="lazy" decoding="async" class="fh-img rounded" style="object-fit: cover;">
                            <p class="fh-help mb-0">Current portrait — upload a new file to replace it.</p>
                        </div>
                    @endif

                    <x-admin.form-row
                        label="Gallery images"
                        for="gallery"
                        :error="$errors->first('gallery')"
                        hint="Multiple files, jpg/png/webp ≤ 2 MB each"
                        help="New images are appended to the gallery and stored as JSON paths."
                    >
                        <input class="fh-field" type="file" id="gallery" name="gallery[]" accept="image/jpeg,image/png,image/webp" multiple>
                    </x-admin.form-row>

                    @if($character->exists && count((array) $character->gallery))
                        <div class="mb-3">
                            <span class="fh-label d-block mb-2">Current gallery</span>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach((array) $character->gallery as $galleryPath)
                                    <div class="text-center">
                                        <img src="{{ asset('storage/'.ltrim($galleryPath, '/')) }}" alt="Gallery image {{ $loop->iteration }} of {{ $character->name }}" width="84" height="84" loading="lazy" decoding="async" class="fh-img rounded" style="object-fit: cover;">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="remove_gallery[]" value="{{ $galleryPath }}" id="remove-gallery-{{ $loop->index }}">
                                            <label class="form-check-label fh-help" for="remove-gallery-{{ $loop->index }}">Remove</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($character->exists)
                        @foreach((array) $character->gallery as $galleryPath)
                            <input type="hidden" name="_current_gallery[]" value="{{ $galleryPath }}">
                        @endforeach
                    @endif

                    <div class="d-flex gap-2 flex-wrap mt-2">
                        <button class="btn btn-fh" type="submit">
                            <i class="bi bi-check-lg me-2" aria-hidden="true"></i>{{ $character->exists ? 'Save changes' : 'Create character' }}
                        </button>
                        <a class="btn btn-fh-ghost" href="{{ route('admin.characters.index') }}">Cancel</a>
                    </div>
                </form>

                @if($character->exists)
                    <div class="fh-divider my-4" aria-hidden="true"></div>

                    <form
                        method="POST"
                        action="{{ route('admin.characters.destroy', $character) }}"
                        data-confirm="Delete {{ $character->name }}? Relationships and gallery images are removed too."
                    >
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-fh-danger" type="submit">
                            <i class="bi bi-trash me-2" aria-hidden="true"></i>Delete character
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="fh-card fh-card-pad">
                <h2 class="fh-card-title">Related characters</h2>

                @unless($character->exists)
                    <p class="fh-help">Save the character first, then you can link allies, rivals and family.</p>
                @else
                    @if($relations->isEmpty())
                        <x-empty title="No relationships" message="Add the first connection below." icon="bi-diagram-3" />
                    @else
                        <ul class="list-unstyled mb-3">
                            @foreach($relations as $relationRow)
                                <li class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <span class="d-flex align-items-center gap-2">
                                        <strong>{{ $relationRow->relatedCharacter?->name ?? 'Unknown' }}</strong>
                                        <x-badge variant="cyan">{{ $relationRow->relation }}</x-badge>
                                    </span>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.characters.update', $character) }}"
                                        data-confirm="Remove the relationship to {{ $relationRow->relatedCharacter?->name ?? 'this character' }}?"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="relation_action" value="delete">
                                        <input type="hidden" name="relation_id" value="{{ $relationRow->id }}">
                                        <button class="btn btn-fh-ghost btn-sm text-danger" type="submit" aria-label="Remove relationship to {{ $relationRow->relatedCharacter?->name ?? 'this character' }}">
                                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ route('admin.characters.update', $character) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="relation_action" value="add">

                        <x-admin.form-row label="Character" for="related_character_id" required :error="$errors->first('related_character_id')">
                            <select class="fh-field" id="related_character_id" name="related_character_id" required>
                                <option value="">Choose a character…</option>
                                @foreach($allCharacters as $other)
                                    <option value="{{ $other->id }}" @selected((int) old('related_character_id') === $other->id)>{{ $other->name }}</option>
                                @endforeach
                            </select>
                        </x-admin.form-row>

                        <x-admin.form-row label="Relationship" for="relation" required :error="$errors->first('relation')">
                            <select class="fh-field" id="relation" name="relation" required>
                                <option value="">Choose…</option>
                                @foreach($relationTypes as $relationType)
                                    <option value="{{ $relationType }}" @selected(old('relation') === $relationType)>{{ ucfirst($relationType) }}</option>
                                @endforeach
                            </select>
                        </x-admin.form-row>

                        <button class="btn btn-fh-outline w-100" type="submit">
                            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add relationship
                        </button>
                    </form>
                @endunless
            </div>
        </div>
    </div>
@endsection
