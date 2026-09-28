<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" data-font-scale="1" data-motion="full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="fh-login-url" content="{{ route('login') }}">
    <meta name="fh-rate-url" content="{{ route('rate') }}">
    <meta name="fh-settings-url" content="{{ route('settings.update') }}">
    <meta name="fh-chat-url" content="{{ route('chat.store') }}">
    <meta name="theme-color" content="#050816">

    <title>@yield('title', 'Fan Hub+ · Explore. Discover. Connect.')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/motion.css') }}">

    @yield('styles')
</head>
<body>
    <a class="fh-skip" href="#main-content">Skip to main content</a>

    <div class="fh-auth">
        <aside class="fh-auth-art" aria-label="About Fan Hub+">
            <a class="fh-brand" href="{{ route('home') }}">
                <span class="fh-brand-mark"><i class="bi bi-stars"></i></span>
                <span>Fan Hub<span class="fh-brand-plus">+</span></span>
            </a>

            <div class="fh-auth-art-copy">
                <span class="fh-eyebrow">Explore. Discover. Connect.</span>
                <h1>Your fandoms, <span class="fh-text-gradient">all in one place</span>.</h1>
                <p>
                    Follow series, characters and events across Anime, Gaming, Movies, TV, K-Pop,
                    Comics, Manga and Cosplay — bookmark what you love and never miss a release.
                </p>
                <div class="fh-hero-actions">
                    <a class="btn btn-fh" href="{{ route('explore') }}">
                        <i class="bi bi-compass"></i> Start exploring
                    </a>
                    <a class="btn btn-fh-ghost" href="{{ route('articles.index') }}">
                        <i class="bi bi-journal-text"></i> Read articles
                    </a>
                </div>
            </div>

            <figure class="fh-auth-art-art">
                <img
                    class="fh-img"
                    src="{{ asset('images/catalogue/banner-search.png') }}"
                    alt="Cinematic Fan Hub+ artwork placeholder"
                    width="960"
                    height="540"
                    loading="lazy"
                    decoding="async"
                >
            </figure>
        </aside>

        <div class="fh-auth-form">
            <main id="main-content" tabindex="-1" style="width:100%;">
                @include('partials.flash')

                <div class="fh-auth-card">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <x-toast-container />

    @php
        $fhUserPayload = auth()->user()
            ? ['id' => auth()->id(), 'name' => auth()->user()->name, 'role' => auth()->user()->role]
            : null;
    @endphp
    <script>
        window.FanHub = window.FanHub || {};
        FanHub.user = @json($fhUserPayload);
    </script>
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    @yield('scripts')
</body>
</html>
