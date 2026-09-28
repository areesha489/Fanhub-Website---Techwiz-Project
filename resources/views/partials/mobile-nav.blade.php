{{-- Mobile / tablet navigation drawer. Same links as the desktop sidebar. --}}
<div class="offcanvas offcanvas-start fh-mobile-nav" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
    <div class="offcanvas-header">
        <a class="fh-brand mb-0" href="{{ route('home') }}" id="mobileNavLabel">
            <span class="fh-brand-mark" aria-hidden="true"><i class="bi bi-stars"></i></span>
            <span>Fan Hub<span class="fh-brand-plus">+</span></span>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close navigation"></button>
    </div>

    <div class="offcanvas-body">
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
        @endphp
        @if($fhAccountShell)
            @include('partials.nav-links')
        @else
            @include('partials.public-nav', ['navMode' => 'drawer'])
        @endif

        <hr class="fh-divider">

        @auth
            <div class="d-flex align-items-center gap-2 mb-3">
                <x-avatar :user="auth()->user()" />
                <div class="min-w-0">
                    <strong class="d-block text-truncate">{{ auth()->user()->name }}</strong>
                    <span class="fh-text-dim fh-text-sm">{{ auth()->user()->email }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" data-confirm="Log out of Fan Hub+?">
                @csrf
                <button class="btn btn-fh-outline w-100" type="submit">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out
                </button>
            </form>
        @else
            <div class="d-grid gap-2">
                <a class="btn btn-fh" href="{{ route('register') }}">
                    <i class="bi bi-stars" aria-hidden="true"></i> Create account
                </a>
                <a class="btn btn-fh-outline" href="{{ route('login') }}">
                    <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Log in
                </a>
            </div>
        @endauth
    </div>
</div>
