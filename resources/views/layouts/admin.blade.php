<!doctype html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-theme="dark"
    data-font-scale="1"
    data-motion="full"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="fh-login-url" content="{{ route('login') }}">
    <meta name="fh-rate-url" content="{{ route('rate') }}">
    <meta name="fh-settings-url" content="{{ route('settings.update') }}">
    <meta name="fh-chat-url" content="{{ route('chat.store') }}">
    <meta name="theme-color" content="#050816">

    <title>@yield('title', 'Admin · Fan Hub+')</title>

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

    <aside class="fh-sidebar fh-admin-sidebar" id="adminSidebar" aria-label="Admin navigation">
        <div class="fh-sidebar-inner">
            <a class="fh-brand" href="{{ route('home') }}">
                <span class="fh-brand-mark"><i class="bi bi-stars"></i></span>
                <span>Fan Hub<span class="fh-brand-plus">+</span></span>
            </a>

            <span class="fh-admin-badge" style="margin: 0 .75rem .5rem;">
                <i class="bi bi-shield-lock"></i> Admin panel
            </span>

            <p class="fh-sidebar-section-title">Overview</p>
            <ul class="fh-sidebar-nav">
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                       href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-grid-1x2-fill"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}"
                       href="{{ route('admin.analytics') }}">
                        <i class="bi bi-graph-up-arrow"></i> Analytics
                    </a>
                </li>
            </ul>

            <p class="fh-sidebar-section-title">Manage</p>
            <ul class="fh-sidebar-nav">
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                       href="{{ route('admin.users.index') }}">
                        <i class="bi bi-people-fill"></i> Users
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                       href="{{ route('admin.categories.index') }}">
                        <i class="bi bi-bookmark-fill"></i> Categories
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.content.*') ? 'active' : '' }}"
                       href="{{ route('admin.content.index') }}">
                        <i class="bi bi-film"></i> Content
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.characters.*') ? 'active' : '' }}"
                       href="{{ route('admin.characters.index') }}">
                        <i class="bi bi-people"></i> Characters
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}"
                       href="{{ route('admin.articles.index') }}">
                        <i class="bi bi-newspaper"></i> Articles
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}"
                       href="{{ route('admin.media.index') }}">
                        <i class="bi bi-play-circle-fill"></i> Media
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}"
                       href="{{ route('admin.events.index') }}">
                        <i class="bi bi-calendar-event-fill"></i> Events
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.merchandise.*') ? 'active' : '' }}"
                       href="{{ route('admin.merchandise.index') }}">
                        <i class="bi bi-bag-fill"></i> Merchandise
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.releases.*') ? 'active' : '' }}"
                       href="{{ route('admin.releases.index') }}">
                        <i class="bi bi-box-seam-fill"></i> Releases
                    </a>
                </li>
            </ul>

            <p class="fh-sidebar-section-title">Moderation</p>
            <ul class="fh-sidebar-nav">
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.submissions.*') ? 'active' : '' }}"
                       href="{{ route('admin.submissions.index') }}">
                        <i class="bi bi-inbox-fill"></i> Fan Submissions
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}"
                       href="{{ route('admin.feedback.index') }}">
                        <i class="bi bi-chat-quote-fill"></i> Feedback
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.chatbot.*') && ! request()->routeIs('admin.chatbot.queries') ? 'active' : '' }}"
                       href="{{ route('admin.chatbot.index') }}">
                        <i class="bi bi-database-fill"></i> Chatbot Knowledge
                    </a>
                </li>
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.chatbot.queries') ? 'active' : '' }}"
                       href="{{ route('admin.chatbot.queries') }}">
                        <i class="bi bi-chat-dots-fill"></i> Chat Logs
                    </a>
                </li>
            </ul>

            <p class="fh-sidebar-section-title">System</p>
            <ul class="fh-sidebar-nav">
                <li>
                    <a class="fh-sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                       href="{{ route('admin.settings.show') }}">
                        <i class="bi bi-gear-fill"></i> Settings
                    </a>
                </li>
            </ul>

            <div class="fh-sidebar-footer">
                <a class="fh-sidebar-link" href="{{ route('home') }}">
                    <i class="bi bi-arrow-left"></i> Back to site
                </a>
            </div>
        </div>
    </aside>

    <div class="fh-content">
        <header class="fh-topbar">
            <button type="button" class="icon-btn fh-admin-burger" data-sidebar-toggle
                    aria-label="Toggle admin navigation" aria-controls="adminSidebar">
                <i class="bi bi-list"></i>
            </button>

            <a class="fh-brand" href="{{ route('admin.dashboard') }}">
                <span class="fh-brand-mark"><i class="bi bi-shield-check"></i></span>
                <span class="fh-brand-text">Fan Hub<span class="fh-brand-plus">+</span> Admin</span>
            </a>

            <div class="fh-topbar-actions">
                <a class="icon-btn" href="{{ route('home') }}" target="_blank" rel="noopener"
                   aria-label="Open the public site in a new tab" title="View site">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>

                <div class="dropdown">
                    <button class="fh-topbar-user dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="Account menu">
                        <x-avatar :user="auth()->user()" size="sm" />
                        <span class="fh-user-name">{{ auth()->user()?->name ?? 'Account' }}</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-header">
                                {{ auth()->user()?->email ?? '' }}
                            </span>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i>My profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('settings.show') }}"><i class="bi bi-sliders me-2"></i>Preferences</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" data-confirm="Log out of Fan Hub+?">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right me-2"></i>Log out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main id="main-content" class="fh-admin-main" tabindex="-1">
            @include('partials.flash')
            @yield('content')
        </main>

        @include('partials.footer')
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
    <script src="{{ asset('assets/vendor/chart.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    @yield('scripts')
</body>
</html>
