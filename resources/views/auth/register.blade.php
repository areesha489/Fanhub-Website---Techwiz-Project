@extends('layouts.guest')

@section('title', 'Create account · Fan Hub+')

@section('content')
    <div class="fh-auth-head">
        <h2>Join Fan Hub+</h2>
        <p>Bookmark what you love, track releases and shape your own corner of the fandom.</p>
    </div>

    @if ($errors->any())
        <x-error class="mb-4" title="Check your details" :message="$errors->first()" />
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="fh-field-group">
            <label class="fh-label" for="register-name">
                Display name <span class="req" aria-hidden="true">*</span>
            </label>
            <input
                id="register-name"
                type="text"
                name="name"
                class="fh-field @error('name') is-invalid @enderror"
                value="{{ old('name') }}"
                placeholder="e.g. NovaRider"
                minlength="3"
                maxlength="60"
                autocomplete="nickname"
                autofocus
                required
                aria-describedby="@error('name') register-name-error @enderror"
            >
            @error('name')
                <div class="fh-invalid-feedback" id="register-name-error">{{ $message }}</div>
            @enderror
            <div class="fh-help">3–60 characters. Shown next to your comments and submissions.</div>
        </div>

        <div class="fh-field-group">
            <label class="fh-label" for="register-email">
                Email address <span class="req" aria-hidden="true">*</span>
            </label>
            <input
                id="register-email"
                type="email"
                name="email"
                class="fh-field @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                autocomplete="email"
                required
                aria-describedby="@error('email') register-email-error @enderror"
            >
            @error('email')
                <div class="fh-invalid-feedback" id="register-email-error">{{ $message }}</div>
            @enderror
            <div class="fh-help">We send a confirmation link here — nothing else.</div>
        </div>

        <div class="fh-field-group">
            <label class="fh-label" for="register-password">
                Password <span class="req" aria-hidden="true">*</span>
            </label>
            <input
                id="register-password"
                type="password"
                name="password"
                class="fh-field @error('password') is-invalid @enderror"
                placeholder="At least 8 characters"
                autocomplete="new-password"
                required
                aria-describedby="register-password-help @error('password') register-password-error @enderror"
            >
            @error('password')
                <div class="fh-invalid-feedback" id="register-password-error">{{ $message }}</div>
            @enderror
            <div class="fh-help" id="register-password-help">
                Minimum 8 characters with at least one letter and one number.
            </div>
        </div>

        <div class="fh-field-group">
            <label class="fh-label" for="register-password-confirmation">
                Confirm password <span class="req" aria-hidden="true">*</span>
            </label>
            <input
                id="register-password-confirmation"
                type="password"
                name="password_confirmation"
                class="fh-field"
                placeholder="Repeat the password"
                autocomplete="new-password"
                required
            >
        </div>

        <div class="fh-field-group">
            <label class="d-flex align-items-start gap-2 fh-label mb-0" for="register-terms">
                <input
                    type="checkbox"
                    class="form-check-input mt-1"
                    id="register-terms"
                    name="terms"
                    value="1"
                    @checked(old('terms'))
                    required
                    aria-describedby="@error('terms') register-terms-error @enderror"
                >
                <span>
                    I agree to the
                    <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms of Service</a>
                    and the
                    <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>.
                    <span class="req" aria-hidden="true">*</span>
                </span>
            </label>
            @error('terms')
                <div class="fh-invalid-feedback" id="register-terms-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-fh w-100">
            <i class="bi bi-stars" aria-hidden="true"></i> Create account
        </button>
    </form>

    <div class="fh-auth-foot">
        Already have an account?
        <a href="{{ route('login') }}">Sign in</a>
    </div>
@endsection
