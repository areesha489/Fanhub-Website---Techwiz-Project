@extends('layouts.guest')

@section('title', 'Choose a new password · Fan Hub+')

@section('content')
    <div class="fh-auth-head">
        <h2>Set a new password</h2>
        <p>Pick something you have not used on Fan Hub+ before.</p>
    </div>

    @if ($errors->any())
        <x-error class="mb-4" title="Check your details" :message="$errors->first()" />
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="fh-field-group">
            <label class="fh-label" for="reset-email">
                Email address <span class="req" aria-hidden="true">*</span>
            </label>
            <input
                id="reset-email"
                type="email"
                name="email"
                class="fh-field @error('email') is-invalid @enderror"
                value="{{ old('email', $email) }}"
                placeholder="you@example.com"
                autocomplete="email"
                autofocus
                required
                aria-describedby="@error('email') reset-email-error @enderror"
            >
            @error('email')
                <div class="fh-invalid-feedback" id="reset-email-error">{{ $message }}</div>
            @enderror
            <div class="fh-help">Use the address the reset link was sent to.</div>
        </div>

        <div class="fh-field-group">
            <label class="fh-label" for="reset-password">
                New password <span class="req" aria-hidden="true">*</span>
            </label>
            <input
                id="reset-password"
                type="password"
                name="password"
                class="fh-field @error('password') is-invalid @enderror"
                placeholder="At least 8 characters"
                autocomplete="new-password"
                required
                aria-describedby="reset-password-help @error('password') reset-password-error @enderror"
            >
            @error('password')
                <div class="fh-invalid-feedback" id="reset-password-error">{{ $message }}</div>
            @enderror
            <div class="fh-help" id="reset-password-help">
                Minimum 8 characters with at least one letter and one number.
            </div>
        </div>

        <div class="fh-field-group">
            <label class="fh-label" for="reset-password-confirmation">
                Confirm new password <span class="req" aria-hidden="true">*</span>
            </label>
            <input
                id="reset-password-confirmation"
                type="password"
                name="password_confirmation"
                class="fh-field"
                placeholder="Repeat the password"
                autocomplete="new-password"
                required
            >
        </div>

        <button type="submit" class="btn btn-fh w-100">
            <i class="bi bi-key" aria-hidden="true"></i> Save new password
        </button>
    </form>

    <div class="fh-auth-foot">
        <a href="{{ route('login') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to sign in</a>
    </div>
@endsection
