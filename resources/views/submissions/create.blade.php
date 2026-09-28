@extends('layouts.app')

@section('title', 'Submit content · Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Dashboard', 'to' => route('dashboard')],
        ['label' => 'Submit content'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Contribute</span>
            <h1 class="fh-page-title">Submit fan content</h1>
            <p class="fh-page-sub">
                Artwork, cosplay, video or a write-up — moderators review every submission
                before it appears on Fan Hub+.
            </p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('submissions.index') }}">
            <i class="bi bi-journal-text" aria-hidden="true"></i> My submissions
        </a>
    </header>

    <div class="fh-layout-with-aside fh-aside-right">
        <div class="fh-panel">
            <p class="fh-panel-title">
                <i class="bi bi-cloud-arrow-up" style="color:#8B5CF6" aria-hidden="true"></i>
                New submission
            </p>

            @if ($errors->any())
                <x-error class="mb-4" title="Check your details" :message="$errors->first()" />
            @endif

            <form method="POST" action="{{ route('submissions.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="fh-field-group">
                    <label class="fh-label" for="submission-title">
                        Title <span class="req" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="submission-title"
                        type="text"
                        name="title"
                        class="fh-field @error('title') is-invalid @enderror"
                        value="{{ old('title') }}"
                        minlength="5"
                        maxlength="255"
                        placeholder="e.g. My Neon Genesis Evangelion poster series"
                        autofocus
                        required
                        aria-describedby="@error('title') submission-title-error @enderror"
                    >
                    @error('title')
                        <div class="fh-invalid-feedback" id="submission-title-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="fh-field-row mb-3">
                    <div class="fh-field-group mb-0">
                        <label class="fh-label" for="submission-type">
                            Type <span class="req" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="submission-type"
                            name="type"
                            class="fh-field @error('type') is-invalid @enderror"
                            required
                            aria-describedby="@error('type') submission-type-error @enderror"
                        >
                            @foreach ($types as $submissionType)
                                <option value="{{ $submissionType }}" @selected(old('type') === $submissionType)>
                                    {{ \Illuminate\Support\Str::headline($submissionType) }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="fh-invalid-feedback" id="submission-type-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="fh-field-group mb-0">
                        <label class="fh-label" for="submission-category">Fandom</label>
                        <select
                            id="submission-category"
                            name="category_id"
                            class="fh-field @error('category_id') is-invalid @enderror"
                            aria-describedby="submission-category-help @error('category_id') submission-category-error @enderror"
                        >
                            <option value="">Not sure / general</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="fh-invalid-feedback" id="submission-category-error">{{ $message }}</div>
                        @enderror
                        <div class="fh-help" id="submission-category-help">Helps the review queue route it faster.</div>
                    </div>
                </div>

                <div class="fh-field-group">
                    <label class="fh-label" for="submission-description">
                        Description <span class="req" aria-hidden="true">*</span>
                        <span class="fh-label-hint"><span id="submission-count">{{ mb_strlen((string) old('description')) }}</span>/5000</span>
                    </label>
                    <textarea
                        id="submission-description"
                        name="description"
                        class="fh-field @error('description') is-invalid @enderror"
                        rows="7"
                        minlength="30"
                        maxlength="5000"
                        placeholder="What is it, which fandom is it for, and anything we should know before publishing it."
                        required
                        aria-describedby="submission-description-help @error('description') submission-description-error @enderror"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <div class="fh-invalid-feedback" id="submission-description-error">{{ $message }}</div>
                    @enderror
                    <div class="fh-help" id="submission-description-help">
                        At least 30 characters. Only submit work you own or have permission to share.
                    </div>
                </div>

                <div class="fh-field-group">
                    <label class="fh-label" for="submission-url">Link <span class="fh-label-hint">optional</span></label>
                    <input
                        id="submission-url"
                        type="url"
                        name="url"
                        class="fh-field @error('url') is-invalid @enderror"
                        value="{{ old('url') }}"
                        placeholder="https://…"
                        maxlength="500"
                        aria-describedby="submission-url-help @error('url') submission-url-error @enderror"
                    >
                    @error('url')
                        <div class="fh-invalid-feedback" id="submission-url-error">{{ $message }}</div>
                    @enderror
                    <div class="fh-help" id="submission-url-help">
                        A page hosting the piece — portfolio, video or gallery.
                    </div>
                </div>

                <div class="fh-field-group">
                    <label class="fh-label" for="submission-image">Image <span class="fh-label-hint">optional</span></label>
                    <input
                        id="submission-image"
                        type="file"
                        name="image"
                        class="fh-field @error('image') is-invalid @enderror"
                        accept="image/jpeg,image/png,image/webp"
                        aria-describedby="submission-image-help @error('image') submission-image-error @enderror"
                    >
                    @error('image')
                        <div class="fh-invalid-feedback" id="submission-image-error">{{ $message }}</div>
                    @enderror
                    <div class="fh-help" id="submission-image-help">
                        JPG, PNG or WebP · up to 2 MB · shown as the preview while we review.
                    </div>
                </div>

                <button type="submit" class="btn btn-fh">
                    <i class="bi bi-send" aria-hidden="true"></i> Send for review
                </button>
            </form>
        </div>

        <aside>
            <div class="fh-panel mb-4">
                <p class="fh-panel-title">
                    <i class="bi bi-signpost-split" style="color:#FBBF24" aria-hidden="true"></i>
                    How review works
                </p>
                <ol class="fh-text-sm fh-text-dim mb-0 ps-3">
                    <li class="mb-2">You send the piece with a short description.</li>
                    <li class="mb-2">An admin checks it for quality and rights.</li>
                    <li class="mb-2">Approved work appears on Fan Hub+, credited to you.</li>
                    <li>Rejected work comes back with a note explaining why.</li>
                </ol>
            </div>

            <div class="fh-panel">
                <p class="fh-panel-title">
                    <i class="bi bi-lightbulb" style="color:#22D3EE" aria-hidden="true"></i>
                    Good to know
                </p>
                <p class="fh-text-sm fh-text-dim mb-0">
                    Submissions are reviewed by people, not bots. Sending the same piece twice
                    only slows the queue — track progress on
                    <a href="{{ route('submissions.index') }}">My submissions</a>.
                </p>
            </div>
        </aside>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var field = document.getElementById('submission-description');
            var counter = document.getElementById('submission-count');
            if (!field || !counter) return;
            field.addEventListener('input', function () {
                counter.textContent = String(field.value.length);
            });
        })();
    </script>
@endsection
