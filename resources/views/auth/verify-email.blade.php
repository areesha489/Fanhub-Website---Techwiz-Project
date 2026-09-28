@extends('layouts.guest')

@section('title', 'Confirm your email · Fan Hub+')

@section('content')
    <div class="fh-auth-head">
        <span class="fh-icon-tile mx-auto mb-3 d-inline-flex" style="background: linear-gradient(135deg, #8B5CF6, #EC4899);">
            <i class="bi bi-envelope-paper" aria-hidden="true"></i>
        </span>
        <h2>Confirm your email</h2>
        <p>
            We sent a confirmation link to
            <strong>{{ $user->email }}</strong>. Open it to activate every feature.
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">
            <i class="bi bi-check-circle me-2" aria-hidden="true"></i>{{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info" role="status">
            <i class="bi bi-info-circle me-2" aria-hidden="true"></i>{{ session('info') }}
        </div>
    @endif

    @if ($errors->any())
        <x-error class="mb-4" title="Something went wrong" :message="$errors->first()" />
    @endif

    @if ($user->hasVerifiedEmail())
        <div class="alert alert-success" role="status">
            <i class="bi bi-patch-check-fill me-2" aria-hidden="true"></i>
            This address is already verified — you are all set.
        </div>

        <a class="btn btn-fh w-100" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2" aria-hidden="true"></i> Go to dashboard
        </a>
    @else
        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="btn btn-fh w-100">
                <i class="bi bi-send" aria-hidden="true"></i> Resend confirmation link
            </button>
        </form>

        <div class="fh-help mt-3">
            <i class="bi bi-lightbulb" aria-hidden="true"></i>
            Development runs with <code>MAIL_MAILER=log</code> — the message lands in
            <code>storage/logs/laravel.log</code>.
        </div>

        <div class="fh-auth-foot">
            Wrong address?
            <a href="{{ route('profile.show') }}">Update it in your profile</a>
            <span class="mx-2" aria-hidden="true">·</span>
            <a href="{{ route('dashboard') }}">Skip for now</a>
        </div>
    @endif
@endsection
