@extends('layouts.admin')

@section('title', $formTitle.' · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">{{ $formTitle }}</h1>
            <p class="fh-page-sub">Slug, icon and accent colour are generated from what you enter here.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.categories.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to categories
        </a>
    </div>

    <div class="fh-card fh-card-pad" style="max-width: 820px;">
        @include('admin.partials.form-errors')

        <form
            method="POST"
            action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @if($category->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <x-admin.form-row
                        label="Name"
                        for="name"
                        required
                        :error="$errors->first('name')"
                        :help="$category->exists ? 'The slug only changes when you change the name.' : 'The slug is generated from the name automatically.'"
                    >
                        <input
                            class="fh-field"
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $category->name) }}"
                            maxlength="120"
                            required
                        >
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Position" for="position" :error="$errors->first('position')" hint="Sidebar order, lowest first">
                        <input
                            class="fh-field"
                            type="number"
                            id="position"
                            name="position"
                            value="{{ old('position', $category->position ?? 0) }}"
                            min="0"
                            max="9999"
                            inputmode="numeric"
                        >
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row
                        label="Bootstrap icon"
                        for="icon"
                        :error="$errors->first('icon')"
                        hint="bi- prefix, e.g. bi-fire"
                    >
                        <div class="d-flex align-items-center gap-2">
                            <input
                                class="fh-field"
                                type="text"
                                id="icon"
                                name="icon"
                                value="{{ old('icon', $category->icon) }}"
                                maxlength="60"
                                placeholder="bi-fire"
                            >
                            <span
                                id="iconPreviewWrap"
                                class="fh-avatar"
                                style="background: rgba(139,92,246,.35); display:inline-flex; align-items:center; justify-content:center; flex: 0 0 auto;"
                                aria-hidden="true"
                            >
                                <i class="bi bi-stars" id="iconPreview"></i>
                            </span>
                        </div>
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Accent colour" for="accent" :error="$errors->first('accent')" hint="Hex value with preset swatches">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <input
                                class="fh-field"
                                type="color"
                                id="accent"
                                name="accent"
                                value="{{ old('accent', $category->accent ?: '#8B5CF6') }}"
                                style="width: 64px; padding: .3rem;"
                                aria-label="Accent colour picker"
                            >
                            <div class="d-flex gap-1 flex-wrap" role="group" aria-label="Accent colour presets">
                                @foreach(['#8B5CF6', '#EC4899', '#22D3EE', '#3B82F6', '#34D399', '#FBBF24', '#FB7185'] as $preset)
                                    <button
                                        type="button"
                                        class="btn p-0 border-0 fh-accent-swatch"
                                        data-accent="{{ $preset }}"
                                        style="width: 26px; height: 26px; border-radius: 50%; background: {{ $preset }}; border: 2px solid rgba(255,255,255,.25);"
                                        aria-label="Use colour {{ $preset }}"
                                        title="{{ $preset }}"
                                    ></button>
                                @endforeach
                            </div>
                        </div>
                    </x-admin.form-row>
                </div>
            </div>

            <x-admin.form-row label="Description" for="description" :error="$errors->first('description')">
                <textarea class="fh-field" id="description" name="description" rows="3" maxlength="2000">{{ old('description', $category->description) }}</textarea>
            </x-admin.form-row>

            <x-admin.form-row
                label="Category image"
                for="image"
                :error="$errors->first('image')"
                hint="Optional — jpg, png or webp up to 2 MB"
            >
                <input class="fh-field" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
            </x-admin.form-row>

            @if($category->image)
                <div class="mb-3">
                    <img
                        src="{{ asset('storage/'.ltrim($category->image, '/')) }}"
                        alt="Current image for {{ $category->name }}"
                        width="160"
                        height="90"
                        loading="lazy"
                        decoding="async"
                        class="fh-img rounded"
                        style="object-fit: cover;"
                    >
                    <p class="fh-help mb-0">Current image — upload a new file to replace it.</p>
                </div>
            @endif

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-fh" type="submit">
                    <i class="bi bi-check-lg me-2" aria-hidden="true"></i>{{ $category->exists ? 'Save changes' : 'Create category' }}
                </button>
                <a class="btn btn-fh-ghost" href="{{ route('admin.categories.index') }}">Cancel</a>
            </div>
        </form>

        @if($category->exists)
            <div class="fh-divider my-4" aria-hidden="true"></div>

            <form
                method="POST"
                action="{{ route('admin.categories.destroy', $category) }}"
                data-confirm="Delete {{ $category->name }}? All content in this category is removed too."
            >
                @csrf
                @method('DELETE')
                <button class="btn btn-fh-danger" type="submit">
                    <i class="bi bi-trash me-2" aria-hidden="true"></i>Delete category
                </button>
            </form>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    (function () {
        var iconInput = document.getElementById('icon');
        var iconPreview = document.getElementById('iconPreview');

        function syncIcon() {
            if (!iconInput || !iconPreview) return;
            var value = iconInput.value.trim();
            var iconName = value ? (value.indexOf('bi-') === 0 ? value : 'bi-' + value) : 'bi-stars';
            iconPreview.className = 'bi ' + iconName;
        }

        if (iconInput) {
            iconInput.addEventListener('input', syncIcon);
            syncIcon();
        }

        var accentInput = document.getElementById('accent');
        Array.prototype.forEach.call(document.querySelectorAll('[data-accent]'), function (swatch) {
            swatch.addEventListener('click', function () {
                if (accentInput) accentInput.value = swatch.getAttribute('data-accent');
            });
        });
    })();
</script>
@endsection
