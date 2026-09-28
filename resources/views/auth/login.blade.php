@extends('layouts.guest')

@section('title', 'Sign in · Fan Hub+')

@section('content')
    <div class="fh-auth-head">
        <h2>Welcome back</h2>
        <p>Sign in to reach your bookmarks, ratings and dashboard.</p>
    </div>

    @if ($errors->any())
        <x-error class="mb-4" title="Check your details" :message="$errors->first()" />
    @endif

    <form method="POST" action="{{ route('login', $next !== null ? ['next' => $next] : []) }}">
        @csrf

        @if ($next !== null)
            <input type="hidden" name="next" value="{{ $next }}">
        @endif

        <div class="fh-field-group">
            <label class="fh-label" for="login-email">
                Email address <span class="req" aria-hidden="true">*</span>
            </label>
            <input
                id="login-email"
                type="email"
                name="email"
                class="fh-field @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                autocomplete="username"
                autofocus
                required
                aria-describedby="login-email-help @error('email') login-email-error @enderror"
            >
            @error('email')
                <div class="fh-invalid-feedback" id="login-email-error">{{ $message }}</div>
            @enderror
            <div class="fh-help" id="login-email-help">The address you registered with.</div>
        </div>

        <div class="fh-field-group">
            <label class="fh-label" for="login-password">
                Password <span class="req" aria-hidden="true">*</span>
            </label>
            <input
                id="login-password"
                type="password"
                name="password"
                class="fh-field @error('password') is-invalid @enderror"
                placeholder="Your password"
                autocomplete="current-password"
                required
                aria-describedby="@error('password') login-password-error @enderror"
            >
            @error('password')
                <div class="fh-invalid-feedback" id="login-password-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
            <label class="d-flex align-items-center gap-2 fh-label mb-0" for="login-remember">
                <input
                    type="checkbox"
                    class="form-check-input mt-0"
                    id="login-remember"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                >
                Remember me
            </label>

            <a class="fh-help text-decoration-none" href="{{ route('password.request') }}">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="btn btn-fh w-100">
            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Sign in
        </button>
    </form>

    <div class="fh-auth-foot">
        <p class="mb-2">New to Fan Hub+?
            <a href="{{ route('register', $next !== null ? ['next' => $next] : []) }}">Create an account</a>
        </p>
        <p class="fh-help mb-0">
            <i class="bi bi-terminal" aria-hidden="true"></i>
            Development seed — admin@fanhubplus.test / FanHub!Admin2026
        </p>
    </div>
@endsection
