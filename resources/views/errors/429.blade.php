@extends('layouts.app')

@section('title', 'Too many requests (429) · Fan Hub+')

@section('content')
    <div class="fh-errorpage">
        <p class="fh-eyebrow">Slow down a little</p>
        <p class="fh-errorpage-code">429</p>
        <h1 class="fh-title mb-2">Too many requests</h1>
        <p class="fh-sub fh-text-center" style="max-width: 52ch;">
            You've made a lot of requests in a short time. Wait a few seconds and try again —
            this protects Fan Hub+ for everyone.
        </p>

        <div class="fh-state-actions mt-3">
            <a class="btn btn-fh" href="{{ url()->current() }}">
                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Reload page
            </a>
            <a class="btn btn-fh-outline" href="{{ route('home') }}">
                <i class="bi bi-house-door" aria-hidden="true"></i> Go home
            </a>
            <a class="btn btn-fh-ghost" href="{{ route('explore') }}">
                <i class="bi bi-compass" aria-hidden="true"></i> Explore
            </a>
        </div>
    </div>
@endsection
