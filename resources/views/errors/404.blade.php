@extends('layouts.app')

@section('title', 'Page not found (404) · Fan Hub+')

@section('content')
    <div class="fh-errorpage">
        <p class="fh-eyebrow">Lost in the multiverse</p>
        <p class="fh-errorpage-code">404</p>
        <h1 class="fh-title mb-2">This page doesn't exist</h1>
        <p class="fh-sub fh-text-center" style="max-width: 52ch;">
            The link may be outdated, or the title you were looking for moved.
            Try searching Fan Hub+ or head back to the homepage.
        </p>

        <div class="fh-state-actions mt-3">
            <a class="btn btn-fh" href="{{ route('home') }}">
                <i class="bi bi-house-door" aria-hidden="true"></i> Go home
            </a>
            <a class="btn btn-fh-outline" href="{{ route('explore') }}">
                <i class="bi bi-compass" aria-hidden="true"></i> Explore
            </a>
            <a class="btn btn-fh-ghost" href="{{ url()->previous() }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back
            </a>
        </div>

        <form class="fh-search mt-4" style="max-width: 30rem;"
              action="{{ route('search') }}" method="GET" role="search">
            <label class="visually-hidden" for="error-search">Search Fan Hub+</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" id="error-search" placeholder="Search titles, characters, events…">
            <button class="btn btn-fh btn-sm" type="submit">Search</button>
        </form>
    </div>
@endsection
