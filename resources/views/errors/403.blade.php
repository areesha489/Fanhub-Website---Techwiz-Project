@extends('layouts.app')

@section('title', 'Access denied (403) · Fan Hub+')

@section('content')
    <div class="fh-errorpage">
        <p class="fh-eyebrow">Members only</p>
        <p class="fh-errorpage-code">403</p>
        <h1 class="fh-title mb-2">You can't access this page</h1>
        <p class="fh-sub fh-text-center" style="max-width: 54ch;">
            Your account doesn't have permission to view this area. If you believe this is a
            mistake, sign in with the right account or contact support.
        </p>

        <div class="fh-state-actions mt-3">
            <a class="btn btn-fh" href="{{ route('home') }}">
                <i class="bi bi-house-door" aria-hidden="true"></i> Go home
            </a>
            @guest
                <a class="btn btn-fh-outline" href="{{ route('login') }}">
                    <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Log in
                </a>
            @else
                <a class="btn btn-fh-outline" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2" aria-hidden="true"></i> My dashboard
                </a>
            @endguest
            <a class="btn btn-fh-ghost" href="{{ url()->previous() }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back
            </a>
        </div>
    </div>
@endsection
