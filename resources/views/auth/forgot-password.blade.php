@extends('layouts.guest')

@section('title', 'Reset password · Fan Hub+')

@section('content')
    <div class="fh-auth-head">
        <h2>Forgot your password?</h2>
        <p>Enter your email and we will send a reset link — valid for one hour.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="status">
            <i class="bi bi-envelope-check me-2" aria-hidden="true"></i>{{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <x-error class="mb-4" title="Check your details" :message="$errors->first()" />
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="fh-field-group">
            <label class="fh-label" for="forgot-email">
                Email address <span class="req" aria-hidden="true">*</span>
            </label>
            <input
                id="forgot-email"
                type="email"
                name="email"
                class="fh-field @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                autocomplete="email"
                autofocus
                required
                aria-describedby="forgot-email-help @error('email') forgot-email-error @enderror"
            >
            @error('email')
                <div class="fh-invalid-feedback" id="forgot-email-error">{{ $message }}</div>
            @enderror
            <div class="fh-help" id="forgot-email-help">
                For privacy we show the same message whether or not the address exists.
            </div>
        </div>

        <button type="submit" class="btn btn-fh w-100">
            <i class="bi bi-send" aria-hidden="true"></i> Send reset link
        </button>
    </form>

    <div class="fh-auth-foot">
        <a href="{{ route('login') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to sign in</a>
        <span class="mx-2" aria-hidden="true">·</span>
        <a href="{{ route('register') }}">Create an account</a>
    </div>
@endsection
