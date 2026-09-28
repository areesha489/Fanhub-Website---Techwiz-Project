@extends('layouts.admin')

@section('title', 'Settings · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">Settings</h1>
            <p class="fh-page-sub">Site-wide toggles stored in the settings table and read by the layout.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-grid-1x2-fill me-2" aria-hidden="true"></i>Back to dashboard
        </a>
    </div>

    <div class="fh-card fh-card-pad" style="max-width: 860px;">
        @include('admin.partials.form-errors')

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <x-admin.form-row label="Site tagline" for="site_tagline" required :error="$errors->first('site_tagline')" help="Shown under the logo in the site header.">
                <input class="fh-field" type="text" id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $values['site_tagline']) }}" maxlength="190" required>
            </x-admin.form-row>

            <x-admin.form-row label="Contact email" for="contact_email" :error="$errors->first('contact_email')" help="Public contact address in the footer.">
                <input class="fh-field" type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $values['contact_email']) }}" maxlength="190">
            </x-admin.form-row>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Posts per page" for="posts_per_page" required :error="$errors->first('posts_per_page')" hint="1 – 100">
                        <input class="fh-field" type="number" id="posts_per_page" name="posts_per_page" value="{{ old('posts_per_page', $values['posts_per_page']) }}" min="1" max="100" inputmode="numeric" required>
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Chatbot" for="chatbot_enabled" help="Disables the widget for everyone.">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="chatbot_enabled" name="chatbot_enabled" value="1" @checked(old('chatbot_enabled', $values['chatbot_enabled'] === '1'))>
                            <label class="form-check-label fh-help" for="chatbot_enabled">Chatbot enabled</label>
                        </div>
                    </x-admin.form-row>
                </div>
            </div>

            <div class="fh-divider my-3" aria-hidden="true"></div>

            <x-admin.form-row label="Announcement bar" for="announcement_bar" :error="$errors->first('announcement_bar')" hint="Leave empty to hide the message even when enabled">
                <textarea class="fh-field" id="announcement_bar" name="announcement_bar" rows="2" maxlength="500">{{ old('announcement_bar', $values['announcement_bar']) }}</textarea>
            </x-admin.form-row>

            <x-admin.form-row label="Visibility" for="announcement_bar_enabled" help="Sits above the header on every page.">
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="announcement_bar_enabled" name="announcement_bar_enabled" value="1" @checked(old('announcement_bar_enabled', $values['announcement_bar_enabled'] === '1'))>
                    <label class="form-check-label fh-help" for="announcement_bar_enabled">Show the announcement bar</label>
                </div>
            </x-admin.form-row>

            <div class="d-flex gap-2 flex-wrap mt-2">
                <button class="btn btn-fh" type="submit">
                    <i class="bi bi-check-lg me-2" aria-hidden="true"></i>Save settings
                </button>
                <a class="btn btn-fh-ghost" href="{{ route('admin.dashboard') }}">Cancel</a>
            </div>
        </form>

        <div class="fh-divider my-4" aria-hidden="true"></div>

        <p class="fh-help mb-0">
            Keys managed here: {{ implode(', ', $keys) }}.
            They are created or updated in place — no other keys can be written from this screen.
        </p>
    </div>
@endsection
