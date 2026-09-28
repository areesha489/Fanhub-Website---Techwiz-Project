@extends('layouts.app')

@section('title', 'Dashboard · Fan Hub+')

@section('content')
    @php
        $firstName = trim((string) preg_split('/\s+/', (string) $user->name)[0]) ?: $user->name;

        $activityIcons = [
            'login' => 'bi-box-arrow-in-right',
            'logout' => 'bi-box-arrow-right',
            'register' => 'bi-stars',
            'profile_updated' => 'bi-person-gear',
            'bookmark_added' => 'bi-bookmark-star-fill',
            'bookmark_removed' => 'bi-bookmark-dash',
            'submission' => 'bi-cloud-arrow-up',
            'rating' => 'bi-star-fill',
            'feedback' => 'bi-chat-square-text',
        ];
    @endphp

    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Dashboard'],
    ]" />

    <header class="fh-page-head">
        <div class="d-flex align-items-center gap-3 min-w-0">
            <x-avatar :user="$user" size="xl" />
            <div class="min-w-0">
                <span class="fh-eyebrow">Your hub</span>
                <h1 class="fh-page-title">Welcome back, {{ $firstName }}!</h1>
                <p class="fh-page-sub">
                    Everything you follow, saved and submitted — all in one place.
                </p>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-fh" href="{{ route('submissions.create') }}">
                <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Submit content
            </a>
            <a class="btn btn-fh-ghost" href="{{ route('profile.show') }}">
                <i class="bi bi-person" aria-hidden="true"></i> Edit profile
            </a>
        </div>
    </header>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card label="Bookmarks" :value="$stats['bookmarks']" icon="bi-bookmark-star-fill" tone="violet" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="Saved articles" :value="$stats['articles']" icon="bi-journal-text" tone="cyan" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="Views recorded" :value="$stats['views']" icon="bi-eye-fill" tone="blue" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="Submissions" :value="$stats['submissions']" icon="bi-cloud-arrow-up-fill" tone="pink" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Recommended --}}
        <div class="col-lg-8">
            <x-section
                title="Recommended for you"
                :subtitle="$favoriteCategories->isNotEmpty() ? 'Picked from the fandoms you follow' : 'Popular right now across Fan Hub+'"
                :href="route('explore')"
                linkText="Explore more"
            >
                @if ($recommended->isEmpty())
                    <x-empty
                        title="Nothing to recommend yet"
                        message="Once content is published it will show up right here."
                        icon="bi-compass"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('explore') }}">Browse the catalogue</a>
                    </x-empty>
                @else
                    <div class="fh-grid">
                        @foreach ($recommended as $item)
                            <x-content-card :item="$item" />
                        @endforeach
                    </div>
                @endif
            </x-section>
        </div>

        <div class="col-lg-4">
            {{-- Favourite fandoms --}}
            <div class="fh-panel mb-4">
                <p class="fh-panel-title">
                    <i class="bi bi-heart-fill" style="color:#EC4899" aria-hidden="true"></i>
                    Favourite fandoms
                </p>

                @if ($favoriteCategories->isEmpty())
                    <x-empty
                        title="No favourites yet"
                        message="Pick the fandoms you care about and your feed gets personal."
                        icon="bi-heart"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('profile.show') }}">Choose fandoms</a>
                    </x-empty>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($favoriteCategories as $category)
                            <a class="fh-chip" href="{{ route('category.show', $category->slug) }}">
                                <i class="bi {{ $category->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                    <a class="fh-help d-inline-block mt-3 text-decoration-none" href="{{ route('profile.show') }}">
                        Manage favourites <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </div>

            {{-- Recent activity --}}
            <div class="fh-panel">
                <p class="fh-panel-title">
                    <i class="bi bi-activity" style="color:#22D3EE" aria-hidden="true"></i>
                    Recent activity
                </p>

                @if ($activities->isEmpty())
                    <x-empty
                        title="No activity yet"
                        message="Sign-ins, bookmarks and submissions will appear here."
                        icon="bi-clock-history"
                    />
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($activities as $activity)
                            <li class="d-flex gap-3 py-2 {{ $loop->last ? '' : 'border-bottom' }} border-opacity-25">
                                <span
                                    class="fh-icon-tile"
                                    style="width:2.1rem;height:2.1rem;font-size:.9rem;background:linear-gradient(135deg, rgba(139,92,246,.28), rgba(236,72,153,.22));"
                                    aria-hidden="true"
                                >
                                    <i class="bi {{ $activityIcons[$activity->action] ?? 'bi-activity' }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="fw-semibold fh-text-sm">
                                        {{ $activityLabels[$activity->action] ?? \Illuminate\Support\Str::headline($activity->action) }}
                                    </div>
                                    <div class="fh-text-sm fh-text-dim fh-clamp-2">
                                        {{ $activity->description }}
                                    </div>
                                    <div class="fh-text-sm fh-text-dim">
                                        {{ $activity->created_at?->diffForHumans() }}
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Bookmarked items --}}
        <div class="col-lg-6">
            <x-section title="Bookmarked items" :href="route('bookmarks.index')" linkText="All bookmarks">
                @if ($recentBookmarks === [])
                    <x-empty
                        title="No bookmarks yet"
                        message="Save titles, characters and events and they will collect here."
                        icon="bi-bookmark"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('explore') }}">Find something to save</a>
                    </x-empty>
                @else
                    <div class="fh-panel p-0 overflow-hidden">
                        <ul class="list-unstyled mb-0">
                            @foreach ($recentBookmarks as $row)
                                <li class="d-flex align-items-center gap-3 px-3 py-2 {{ $loop->last ? '' : 'border-bottom' }} border-opacity-25">
                                    <img
                                        class="fh-img rounded"
                                        src="{{ img($row['image'], $row['title'], 120, 90, $row['type_slug']) }}"
                                        alt=""
                                        width="56"
                                        height="42"
                                        loading="lazy"
                                        decoding="async"
                                        style="width:56px;height:42px;object-fit:cover;flex:0 0 auto;"
                                    >
                                    <div class="min-w-0 flex-grow-1">
                                        @if ($row['url'])
                                            <a class="fw-semibold fh-text-sm fh-clamp-2 text-decoration-none" href="{{ $row['url'] }}">
                                                {{ $row['title'] }}
                                            </a>
                                        @else
                                            <span class="fw-semibold fh-text-sm fh-clamp-2">{{ $row['title'] }}</span>
                                        @endif
                                        <div class="fh-text-sm fh-text-dim fh-clamp-2">
                                            {{ $row['subtitle'] !== '' ? $row['subtitle'] : $row['type_label'] }}
                                        </div>
                                    </div>
                                    <x-badge :variant="$row['removed'] ? 'slate' : 'violet'">{{ $row['type_label'] }}</x-badge>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-section>
        </div>

        {{-- Recently viewed --}}
        <div class="col-lg-6">
            <x-section title="Recently viewed">
                @if ($recentViews === [])
                    <x-empty
                        title="Nothing yet — start exploring"
                        message="Titles you open will be listed here for a quick way back."
                        icon="bi-eye"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('search') }}">Search the catalogue</a>
                    </x-empty>
                @else
                    <div class="fh-panel p-0 overflow-hidden">
                        <ul class="list-unstyled mb-0">
                            @foreach ($recentViews as $row)
                                <li class="d-flex align-items-center gap-3 px-3 py-2 {{ $loop->last ? '' : 'border-bottom' }} border-opacity-25">
                                    <span class="fh-icon-tile" style="width:2.1rem;height:2.1rem;font-size:.85rem;background:rgba(59,130,246,.2);" aria-hidden="true">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                    <div class="min-w-0 flex-grow-1">
                                        <a class="fw-semibold fh-text-sm fh-clamp-2 text-decoration-none" href="{{ $row['url'] }}">
                                            {{ $row['title'] }}
                                        </a>
                                        <div class="fh-text-sm fh-text-dim">
                                            {{ $row['type_label'] }} · viewed {{ $row['viewed_at']?->diffForHumans() }}
                                        </div>
                                    </div>
                                    <i class="bi bi-arrow-right fh-text-dim" aria-hidden="true"></i>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-section>
        </div>
    </div>

    {{-- Saved articles --}}
    <x-section title="Saved articles" :href="route('bookmarks.index', ['type' => 'article'])" linkText="See all">
        @if ($savedArticles === [])
            <x-empty
                title="No saved articles"
                message="Bookmark an article while reading and it waits for you here."
                icon="bi-journal-text"
            >
                <a class="btn btn-fh-ghost btn-sm" href="{{ route('articles.index') }}">Read articles</a>
            </x-empty>
        @else
            <div class="fh-grid-3 fh-grid">
                @foreach ($savedArticles as $row)
                    <article class="fh-card fh-card-hover h-100">
                        <div class="fh-card-media fh-media fh-media-16x9">
                            <a href="{{ $row['url'] }}" aria-label="Open {{ $row['title'] }}">
                                <img
                                    class="fh-img"
                                    src="{{ img($row['image'], $row['title'], 640, 360, 'article') }}"
                                    alt="{{ $row['title'] }}"
                                    width="640"
                                    height="360"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </a>
                        </div>
                        <div class="fh-card-body">
                            <h3 class="fh-card-title fh-clamp-2">
                                <a href="{{ $row['url'] }}">{{ $row['title'] }}</a>
                            </h3>
                            <div class="fh-card-meta">
                                <span class="fh-meta-item fh-text-dim fh-text-sm">
                                    <i class="bi bi-collection" aria-hidden="true"></i>
                                    {{ $row['subtitle'] !== '' ? $row['subtitle'] : 'Article' }}
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </x-section>

    <div class="row g-4">
        <div class="col-lg-6">
            <x-section title="Saved characters" :href="route('bookmarks.index', ['type' => 'character'])" linkText="See all">
                @if ($savedCharacters === [])
                    <x-empty
                        title="No saved characters"
                        message="Bookmark a character profile and it lands here."
                        icon="bi-people"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('characters.index') }}">Browse characters</a>
                    </x-empty>
                @else
                    <div class="fh-panel p-0 overflow-hidden">
                        <ul class="list-unstyled mb-0">
                            @foreach ($savedCharacters as $row)
                                <li class="d-flex align-items-center gap-3 px-3 py-2 {{ $loop->last ? '' : 'border-bottom' }} border-opacity-25">
                                    <img
                                        class="fh-img rounded"
                                        src="{{ img($row['image'], $row['title'], 96, 96, 'character') }}"
                                        alt=""
                                        width="42"
                                        height="42"
                                        loading="lazy"
                                        decoding="async"
                                        style="width:42px;height:42px;object-fit:cover;flex:0 0 auto;"
                                    >
                                    <div class="min-w-0 flex-grow-1">
                                        <a class="fw-semibold fh-text-sm fh-clamp-2 text-decoration-none" href="{{ $row['url'] }}">
                                            {{ $row['title'] }}
                                        </a>
                                        <div class="fh-text-sm fh-text-dim fh-clamp-2">{{ $row['subtitle'] }}</div>
                                    </div>
                                    <i class="bi bi-arrow-right fh-text-dim" aria-hidden="true"></i>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-section>
        </div>

        <div class="col-lg-6">
            <x-section title="Saved media" :href="route('bookmarks.index', ['type' => 'media'])" linkText="See all">
                @if ($savedMedia === [])
                    <x-empty
                        title="No saved media"
                        message="Trailers, videos and galleries you bookmark show up here."
                        icon="bi-play-circle"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('media.index') }}">Open multimedia</a>
                    </x-empty>
                @else
                    <div class="fh-panel p-0 overflow-hidden">
                        <ul class="list-unstyled mb-0">
                            @foreach ($savedMedia as $row)
                                <li class="d-flex align-items-center gap-3 px-3 py-2 {{ $loop->last ? '' : 'border-bottom' }} border-opacity-25">
                                    <img
                                        class="fh-img rounded"
                                        src="{{ img($row['image'], $row['title'], 120, 90, 'media') }}"
                                        alt=""
                                        width="56"
                                        height="42"
                                        loading="lazy"
                                        decoding="async"
                                        style="width:56px;height:42px;object-fit:cover;flex:0 0 auto;"
                                    >
                                    <div class="min-w-0 flex-grow-1">
                                        <a class="fw-semibold fh-text-sm fh-clamp-2 text-decoration-none" href="{{ $row['url'] }}">
                                            {{ $row['title'] }}
                                        </a>
                                        <div class="fh-text-sm fh-text-dim fh-clamp-2">{{ $row['subtitle'] }}</div>
                                    </div>
                                    <i class="bi bi-arrow-right fh-text-dim" aria-hidden="true"></i>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-section>
        </div>
    </div>
@endsection
