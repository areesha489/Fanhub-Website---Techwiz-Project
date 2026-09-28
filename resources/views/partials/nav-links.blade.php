{{--
    Shared navigation used by the desktop sidebar and the mobile drawer.
    Order matches the Fan Hub+ reference: Home, Explore, eight fandoms, then discovery and account.
--}}
@php
    $navCategories = collect();

    try {
        $navCategories = \App\Models\Category::nav();
    } catch (\Throwable $e) {
        $navCategories = collect();
    }

    $fhIcon = function ($value, string $fallback = 'bi-collection-fill'): string {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        return str_starts_with($value, 'bi-') ? $value : 'bi-'.$value;
    };

    $fhActive = fn (string ...$patterns): string => request()->routeIs(...$patterns) ? 'active' : '';

    $accountHref = auth()->check() ? route('dashboard') : route('login');
    $bookmarkHref = auth()->check() ? route('bookmarks.index') : route('login');
    $settingsHref = auth()->check() ? route('settings.show') : route('login');
@endphp

<ul class="fh-sidebar-nav">
    <li>
        <a class="fh-sidebar-link {{ $fhActive('home') }}"
           href="{{ route('home') }}"
           @if(request()->routeIs('home')) aria-current="page" @endif>
            <i class="bi bi-house-door"></i>
            <span>Home</span>
        </a>
    </li>
    <li>
        <a class="fh-sidebar-link {{ $fhActive('explore', 'search') }}"
           href="{{ route('explore') }}">
            <i class="bi bi-compass"></i>
            <span>Explore</span>
        </a>
    </li>

    @forelse($navCategories as $navCategory)
        @php
            $navSlug = data_get($navCategory, 'slug');
            $isCurrentCategory = request()->routeIs('category.show')
                && (string) request()->route('slug') === (string) $navSlug;
        @endphp
        <li>
            <a class="fh-sidebar-link {{ $isCurrentCategory ? 'active' : '' }}"
               href="{{ route('category.show', $navSlug) }}"
               @if($isCurrentCategory) aria-current="page" @endif>
                <i class="bi {{ $fhIcon(data_get($navCategory, 'icon')) }}"></i>
                <span>{{ data_get($navCategory, 'name') }}</span>
            </a>
        </li>
    @empty
        <li>
            <a class="fh-sidebar-link" href="{{ route('explore') }}">
                <i class="bi bi-collection"></i>
                <span>Categories</span>
            </a>
        </li>
    @endforelse

    <li>
        <a class="fh-sidebar-link {{ $fhActive('events.*') }}" href="{{ route('events.index') }}">
            <i class="bi bi-calendar-event"></i>
            <span>Events</span>
        </a>
    </li>
    <li>
        <a class="fh-sidebar-link {{ $fhActive('merchandise.*') }}" href="{{ route('merchandise.index') }}">
            <i class="bi bi-bag"></i>
            <span>Merchandise</span>
        </a>
    </li>
    <li>
        <a class="fh-sidebar-link {{ $fhActive('releases.*') }}" href="{{ route('releases.index') }}">
            <i class="bi bi-rocket-takeoff"></i>
            <span>Upcoming Releases</span>
        </a>
    </li>
    <li>
        <a class="fh-sidebar-link {{ $fhActive('articles.*') }}" href="{{ route('articles.index') }}">
            <i class="bi bi-newspaper"></i>
            <span>Articles</span>
        </a>
    </li>
    <li>
        <a class="fh-sidebar-link {{ $fhActive('community.*') }}" href="{{ route('community.index') }}">
            <i class="bi bi-people"></i>
            <span>Community</span>
        </a>
    </li>
    <li>
        <a class="fh-sidebar-link {{ $fhActive('media.*') }}" href="{{ route('media.index') }}">
            <i class="bi bi-collection-play"></i>
            <span>Multimedia</span>
        </a>
    </li>
    <li>
        <a class="fh-sidebar-link {{ $fhActive('dashboard', 'profile.*') }}" href="{{ $accountHref }}">
            <i class="bi bi-person"></i>
            <span>My Account</span>
        </a>
    </li>
    <li>
        <a class="fh-sidebar-link {{ $fhActive('bookmarks.*') }}" href="{{ $bookmarkHref }}">
            <i class="bi bi-bookmark"></i>
            <span>Bookmarks</span>
        </a>
    </li>
    <li>
        <a class="fh-sidebar-link {{ $fhActive('settings.*') }}" href="{{ $settingsHref }}">
            <i class="bi bi-gear"></i>
            <span>Settings</span>
        </a>
    </li>
</ul>
