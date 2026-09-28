{{--
    Glass topbar: brand, global search, notifications, bookmarks, account menu.
    The hamburger opens the mobile offcanvas (#mobileNav) below 1200px, where the
    fixed sidebar is hidden.
--}}
@php
    $fhUnread = collect();
    $fhUnreadCount = 0;

    try {
        if (auth()->check()) {
            $fhUser = auth()->user();
            $fhUnreadCount = (int) $fhUser->notifications()->whereNull('read_at')->count();
            $fhUnread = $fhUser->notifications()->whereNull('read_at')->latest()->limit(5)->get();
        }
    } catch (\Throwable $e) {
        // Notifications table not ready yet — bell simply shows no badge.
    }
@endphp

<header class="fh-topbar">
    <button
        type="button"
        class="icon-btn fh-burger"
        data-bs-toggle="offcanvas"
        data-bs-target="#mobileNav"
        aria-controls="mobileNav"
        aria-label="Open navigation menu"
    >
        <i class="bi bi-list"></i>
    </button>

    <a class="fh-brand fh-brand-desktop-hidden" href="{{ route('home') }}">
        <span class="fh-brand-mark" aria-hidden="true"><i class="bi bi-stars"></i></span>
        <span class="fh-brand-text">Fan Hub<span class="fh-brand-plus">+</span></span>
    </a>

    <div class="fh-topbar-search">
        <form class="fh-search" action="{{ route('search') }}" method="GET" role="search">
            <label class="visually-hidden" for="topbar-search-input">Search Fan Hub+</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                id="topbar-search-input"
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search for anime, movies, characters, etc..."
                autocomplete="off"
            >
            <button class="btn btn-fh btn-sm" type="submit">
                <span class="d-none d-sm-inline">Search</span>
                <i class="bi bi-search d-sm-none"></i>
            </button>
        </form>
    </div>

    <div class="fh-topbar-actions">
        @auth
            {{-- Notifications --}}
            <div class="dropdown">
                <button
                    type="button"
                    class="icon-btn fh-bell"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Notifications{{ $fhUnreadCount > 0 ? ', ' . $fhUnreadCount . ' unread' : '' }}"
                >
                    <i class="bi bi-bell"></i>
                    @if($fhUnreadCount > 0)
                        <span class="fh-bell-count">{{ $fhUnreadCount > 99 ? '99+' : $fhUnreadCount }}</span>
                    @endif
                </button>

                <div class="dropdown-menu dropdown-menu-end fh-notif-list">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        @if($fhUnreadCount > 0)
                            <form method="POST" action="{{ route('notifications.readAll') }}">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
                            </form>
                        @endif
                    </div>

                    @forelse($fhUnread as $fhNote)
                        <form method="POST" action="{{ route('notifications.read', $fhNote->id) }}">
                            @csrf
                            <button type="submit" class="dropdown-item fh-notif-item text-start w-100 border-0">
                                <i class="bi bi-bell-fill" aria-hidden="true"></i>
                                <span>
                                    <span class="fh-notif-title">{{ $fhNote->title }}</span>
                                    @if(data_get($fhNote, 'body'))
                                        <span class="fh-notif-body">{{ \Illuminate\Support\Str::limit(data_get($fhNote, 'body'), 90) }}</span>
                                    @endif
                                    <span class="fh-notif-time">
                                        {{ data_get($fhNote, 'created_at') ? \Illuminate\Support\Carbon::parse(data_get($fhNote, 'created_at'))->diffForHumans() : '' }}
                                    </span>
                                </span>
                            </button>
                        </form>
                    @empty
                        <p class="fh-notif-item mb-0 text-center">
                            <span class="fh-notif-body">You're all caught up.</span>
                        </p>
                    @endforelse

                    <hr class="dropdown-divider my-1">
                    <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">
                        View all notifications <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            {{-- Bookmarks --}}
            <a class="icon-btn" href="{{ route('bookmarks.index') }}" aria-label="My bookmarks" title="Bookmarks">
                <i class="bi bi-bookmark"></i>
            </a>

            {{-- Account --}}
            <div class="dropdown">
                <button
                    type="button"
                    class="fh-topbar-user dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Account menu for {{ auth()->user()->name }}"
                >
                    <x-avatar :user="auth()->user()" size="sm" />
                    <span class="fh-user-name">{{ auth()->user()->name }}</span>
                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <span class="dropdown-header">
                            {{ auth()->user()->name }}
                            @if(auth()->user()->role === 'admin')
                                · Admin
                            @endif
                        </span>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-2" aria-hidden="true"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="bi bi-person me-2" aria-hidden="true"></i>My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('settings.show') }}">
                            <i class="bi bi-sliders me-2" aria-hidden="true"></i>Settings
                        </a>
                    </li>
                    @if(auth()->user()->role === 'admin')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-shield-lock me-2" aria-hidden="true"></i>Admin Panel
                            </a>
                        </li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" data-confirm="Log out of Fan Hub+?">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit">
                                <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Log Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            <a class="btn btn-fh-ghost btn-sm" href="{{ route('login') }}">
                <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Login
            </a>
            <a class="btn btn-fh btn-sm" href="{{ route('register') }}">
                Register
            </a>
        @endauth
    </div>
</header>
