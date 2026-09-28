@extends('layouts.admin')

@section('title', $formTitle.' · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">{{ $formTitle }}</h1>
            <p class="fh-page-sub">The question, the answer and the keywords that trigger it.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.chatbot.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to knowledge
        </a>
    </div>

    <div class="fh-card fh-card-pad" style="max-width: 860px;">
        @include('admin.partials.form-errors')

        <form
            method="POST"
            action="{{ $knowledge->exists ? route('admin.chatbot.update', $knowledge) : route('admin.chatbot.store') }}"
        >
            @csrf
            @if($knowledge->exists)
                @method('PUT')
            @endif

            <x-admin.form-row label="Question" for="question" required :error="$errors->first('question')" hint="Phrased the way a member would type it">
                <input class="fh-field" type="text" id="question" name="question" value="{{ old('question', $knowledge->question) }}" maxlength="300" required>
            </x-admin.form-row>

            <x-admin.form-row label="Answer" for="answer" required :error="$errors->first('answer')">
                <textarea class="fh-field" id="answer" name="answer" rows="6" maxlength="4000" required>{{ old('answer', $knowledge->answer) }}</textarea>
            </x-admin.form-row>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Category" for="category" :error="$errors->first('category')">
                        <input class="fh-field" type="text" id="category" name="category" value="{{ old('category', $knowledge->category) }}" maxlength="120" placeholder="Account, catalogue, events…">
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Available" for="is_active" help="Inactive entries stay saved but are not used">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $knowledge->is_active ?? true))>
                            <label class="form-check-label fh-help" for="is_active">Answer with this entry</label>
                        </div>
                    </x-admin.form-row>
                </div>
            </div>

            <x-admin.form-row label="Keywords" for="keywords" :error="$errors->first('keywords')" hint="Comma separated triggers">
                <input class="fh-field" type="text" id="keywords" name="keywords" value="{{ old('keywords', $knowledge->keywords) }}" maxlength="500" placeholder="password, reset, login">
            </x-admin.form-row>

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-fh" type="submit">
                    <i class="bi bi-check-lg me-2" aria-hidden="true"></i>{{ $knowledge->exists ? 'Save changes' : 'Create entry' }}
                </button>
                <a class="btn btn-fh-ghost" href="{{ route('admin.chatbot.index') }}">Cancel</a>
            </div>
        </form>

        @if($knowledge->exists)
            <div class="fh-divider my-4" aria-hidden="true"></div>

            <form
                method="POST"
                action="{{ route('admin.chatbot.destroy', $knowledge) }}"
                data-confirm="Delete this knowledge entry? The chatbot stops answering with it."
            >
                @csrf
                @method('DELETE')
                <button class="btn btn-fh-danger" type="submit">
                    <i class="bi bi-trash me-2" aria-hidden="true"></i>Delete entry
                </button>
            </form>
        @endif
    </div>
@endsection
