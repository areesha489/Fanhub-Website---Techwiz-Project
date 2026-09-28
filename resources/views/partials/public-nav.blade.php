{{--
    Main-site navigation. Desktop renders a horizontal bar; the mobile drawer
    uses the same links with categories collapsed into a menu.
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

    $navMode = $navMode ?? 'bar';
    $categoryOpen = request()->routeIs('category.show');

    $navItems = [
        ['label' => 'Home', 'href' => route('home'), 'icon' => 'bi-house-door', 'active' => $fhActive('home'), 'current' => request()->routeIs('home')],
        ['label' => 'Explore', 'href' => route('explore'), 'icon' => 'bi-compass', 'active' => $fhActive('explore', 'search'), 'current' => request()->routeIs('explore', 'search')],
        ['label' => 'Events', 'href' => route('events.index'), 'icon' => 'bi-calendar-event', 'active' => $fhActive('events.*'), 'current' => request()->routeIs('events.*')],
        ['label' => 'Articles', 'href' => route('articles.index'), 'icon' => 'bi-newspaper', 'active' => $fhActive('articles.*'), 'current' => request()->routeIs('articles.*')],
        ['label' => 'Community', 'href' => route('community.index'), 'icon' => 'bi-people', 'active' => $fhActive('community.*'), 'current' => request()->routeIs('community.*')],
        ['label' => 'Multimedia', 'href' => route('media.index'), 'icon' => 'bi-collection-play', 'active' => $fhActive('media.*'), 'current' => request()->routeIs('media.*')],
        ['label' => 'Merchandise', 'href' => route('merchandise.index'), 'icon' => 'bi-bag', 'active' => $fhActive('merchandise.*'), 'current' => request()->routeIs('merchandise.*')],
        ['label' => 'Releases', 'href' => route('releases.index'), 'icon' => 'bi-rocket-takeoff', 'active' => $fhActive('releases.*'), 'current' => request()->routeIs('releases.*')],
    ];
@endphp

@if($navMode === 'bar')
    <nav class="fh-navbar" aria-label="Primary">
        <ul class="fh-navbar-list">
            @foreach(array_slice($navItems, 0, 2) as $item)
                <li>
                    <a class="fh-nav-link {{ $item['active'] }}" href="{{ $item['href'] }}" @if($item['current']) aria-current="page" @endif>
                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach

            <li class="dropdown">
                <button
                    class="fh-nav-link dropdown-toggle {{ $categoryOpen ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-haspopup="true"
                >
                    <i class="bi bi-grid" aria-hidden="true"></i>
                    <span>Categories</span>
                </button>
                <ul class="dropdown-menu fh-navbar-menu">
                    @forelse($navCategories as $navCategory)
                        @php
                            $navSlug = data_get($navCategory, 'slug');
                            $isCurrentCategory = $categoryOpen && (string) request()->route('slug') === (string) $navSlug;
                        @endphp
                        <li>
                            <a
                                class="dropdown-item {{ $isCurrentCategory ? 'active' : '' }}"
                                href="{{ route('category.show', $navSlug) }}"
                                @if($isCurrentCategory) aria-current="page" @endif
                            >
                                <i class="bi {{ $fhIcon(data_get($navCategory, 'icon')) }}" aria-hidden="true"></i>
                                {{ data_get($navCategory, 'name') }}
                            </a>
                        </li>
                    @empty
                        <li>
                            <a class="dropdown-item" href="{{ route('explore') }}">Browse categories</a>
                        </li>
                    @endforelse
                </ul>
            </li>

            @foreach(array_slice($navItems, 2) as $item)
                <li>
                    <a class="fh-nav-link {{ $item['active'] }}" href="{{ $item['href'] }}" @if($item['current']) aria-current="page" @endif>
                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@else
    <ul class="fh-sidebar-nav">
        @foreach(array_slice($navItems, 0, 2) as $item)
            <li>
                <a class="fh-sidebar-link {{ $item['active'] }}" href="{{ $item['href'] }}" @if($item['current']) aria-current="page" @endif>
                    <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach

        <li>
            <button
                class="fh-sidebar-link fh-drawer-toggle w-100 {{ $categoryOpen ? 'active' : '' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#publicNavCategories"
                aria-expanded="{{ $categoryOpen ? 'true' : 'false' }}"
                aria-controls="publicNavCategories"
            >
                <i class="bi bi-grid" aria-hidden="true"></i>
                <span>Categories</span>
                <i class="bi bi-chevron-down ms-auto" aria-hidden="true"></i>
            </button>
            <div class="collapse {{ $categoryOpen ? 'show' : '' }}" id="publicNavCategories">
                <ul class="fh-drawer-sub">
                    @forelse($navCategories as $navCategory)
                        @php
                            $navSlug = data_get($navCategory, 'slug');
                            $isCurrentCategory = $categoryOpen && (string) request()->route('slug') === (string) $navSlug;
                        @endphp
                        <li>
                            <a
                                class="fh-sidebar-link {{ $isCurrentCategory ? 'active' : '' }}"
                                href="{{ route('category.show', $navSlug) }}"
                                @if($isCurrentCategory) aria-current="page" @endif
                            >
                                <i class="bi {{ $fhIcon(data_get($navCategory, 'icon')) }}" aria-hidden="true"></i>
                                <span>{{ data_get($navCategory, 'name') }}</span>
                            </a>
                        </li>
                    @empty
                        <li>
                            <a class="fh-sidebar-link" href="{{ route('explore') }}">
                                <i class="bi bi-collection" aria-hidden="true"></i>
                                <span>Categories</span>
                            </a>
                        </li>
                    @endforelse
                </ul>
            </div>
        </li>

        @foreach(array_slice($navItems, 2) as $item)
            <li>
                <a class="fh-sidebar-link {{ $item['active'] }}" href="{{ $item['href'] }}" @if($item['current']) aria-current="page" @endif>
                    <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
