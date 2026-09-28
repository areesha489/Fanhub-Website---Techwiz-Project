@extends('layouts.app')

@section('title', 'Session expired (419) · Fan Hub+')

@section('content')
    <div class="fh-errorpage">
        <p class="fh-eyebrow">Session expired</p>
        <p class="fh-errorpage-code">419</p>
        <h1 class="fh-title mb-2">Your session timed out</h1>
        <p class="fh-sub fh-text-center" style="max-width: 52ch;">
            For security, forms expire after a period of inactivity. Go back, refresh the page
            and submit the form again.
        </p>

        <div class="fh-state-actions mt-3">
            <a class="btn btn-fh" href="{{ url()->previous() }}">
                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Try again
            </a>
            <a class="btn btn-fh-outline" href="{{ route('home') }}">
                <i class="bi bi-house-door" aria-hidden="true"></i> Go home
            </a>
        </div>
    </div>
@endsection
