{{--
    Desktop fixed sidebar (≥1200px). Below that width it is toggled as an overlay
    by assets/js/app.js ([data-sidebar-toggle]) — the mobile offcanvas handles phones.
--}}
<aside class="fh-sidebar" id="sidebar" aria-label="Primary navigation">
    <div class="fh-sidebar-inner">
        <a class="fh-brand" href="{{ route('home') }}">
            <span class="fh-brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" width="18" height="18" aria-hidden="true">
                    <path fill="currentColor" d="M6 6h8.2c4.6 0 7.6 2.5 7.6 6.3 0 2.5-1.3 4.4-3.5 5.4L24.8 26h-5.2l-5.4-7.6H11V26H6V6zm5 4.1v5.2h2.7c2 0 3.2-1 3.2-2.6s-1.2-2.6-3.2-2.6H11z"/>
                </svg>
            </span>
            <span class="fh-brand-copy">
                <span class="fh-brand-text">Fan Hub<span class="fh-brand-plus">+</span></span>
                <span class="fh-brand-tag">Explore. Discover. Connect.</span>
            </span>
        </a>

        @include('partials.nav-links')

        <div class="fh-sidebar-footer">
            <div class="fh-sidebar-promo">
                <strong>Join Our Community</strong>
                <span>Be part of a growing fandom, share your passion and connect with others.</span>
                @auth
                    <a class="btn btn-fh btn-sm w-100 mt-2" href="{{ route('dashboard') }}">Open dashboard</a>
                @else
                    <a class="btn btn-fh btn-sm w-100 mt-2" href="{{ route('register') }}">Sign Up Now</a>
                @endauth
            </div>
        </div>
    </div>
</aside>
