<!doctype html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @php
        $fhTheme = 'dark';
        $fhFontScale = '1';
        $fhMotion = 'full';

        try {
            $fhPrefs = auth()->user()?->preferences;

            if ($fhPrefs) {
                $fhTheme = $fhPrefs->theme ?: 'dark';
                $fhFontScale = (string) (float) ($fhPrefs->font_scale ?: 1);
                $fhMotion = $fhPrefs->reduced_motion ? 'reduced' : 'full';
            }
        } catch (\Throwable $e) {
            // Preferences table may not exist yet — safe defaults.
        }
    @endphp
    data-theme="{{ $fhTheme }}"
    data-font-scale="{{ $fhFontScale }}"
    data-motion="{{ $fhMotion }}"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="fh-login-url" content="{{ route('login') }}">
    <meta name="fh-rate-url" content="{{ route('rate') }}">
    <meta name="fh-settings-url" content="{{ route('settings.update') }}">
    <meta name="fh-bookmark-update-base" content="{{ url('/bookmarks') }}">
    <meta name="fh-chat-url" content="{{ route('chat.store') }}">
    <meta name="theme-color" content="#050816">

    <title>@yield('title', 'Fan Hub+ · Explore. Discover. Connect.')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/reference.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/motion.css') }}">

    @yield('styles')
</head>
@php
        $fhAccountShell = request()->routeIs(
            'dashboard',
            'profile.*',
            'settings.*',
            'bookmarks.*',
            'notifications.*',
            'submissions.*',
            'feedback.*',
        );
        $fhBodyClass = trim(
            (request()->routeIs('home') ? 'fh-page-home' : '')
            .($fhAccountShell ? '' : ' fh-shell-public')
        );
    @endphp
<body @if($fhBodyClass !== '') class="{{ $fhBodyClass }}" @endif>
    <a class="fh-skip" href="#main-content">Skip to main content</a>

    @if($fhAccountShell)
        @include('partials.sidebar')
    @endif

    <div class="fh-content">
        <div class="fh-mast">
            @include('partials.topbar')
            @unless($fhAccountShell)
                @include('partials.public-nav', ['navMode' => 'bar'])
            @endunless
        </div>

        <main id="main-content" tabindex="-1">
            @yield('content')
        </main>

        @include('partials.footer')
    </div>

    @include('partials.mobile-nav')
    @include('partials.flash')
    @include('partials.chat-widget')
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
