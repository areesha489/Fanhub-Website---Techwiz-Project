@extends('layouts.app')

@section('title', 'Server error (500) · Fan Hub+')

@section('content')
    <div class="fh-errorpage">
        <p class="fh-eyebrow">Technical difficulties</p>
        <p class="fh-errorpage-code">500</p>
        <h1 class="fh-title mb-2">Something broke on our side</h1>
        <p class="fh-sub fh-text-center" style="max-width: 54ch;">
            Our team has been notified. Refresh the page in a moment — if it keeps failing,
            head back to the homepage or send us a note through the feedback form.
        </p>

        <div class="fh-state-actions mt-3">
            <a class="btn btn-fh" href="{{ route('home') }}">
                <i class="bi bi-house-door" aria-hidden="true"></i> Go home
            </a>
            <a class="btn btn-fh-outline" href="{{ route('explore') }}">
                <i class="bi bi-compass" aria-hidden="true"></i> Explore
            </a>
            <a class="btn btn-fh-ghost" href="{{ route('feedback.create') }}">
                <i class="bi bi-chat-quote" aria-hidden="true"></i> Report a problem
            </a>
        </div>
    </div>
@endsection
