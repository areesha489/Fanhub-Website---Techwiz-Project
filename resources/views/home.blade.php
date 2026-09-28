@extends('layouts.app')

@section('title', 'Fan Hub+ — Explore. Discover. Connect.')

@section('content')
    @if($homeError)
        <x-error :message="$homeError" class="mb-4" />
    @endif

    <div class="fh-dash">
        <div class="fh-dash-main">
            <x-hero :slides="$heroSlides" class="fh-hero-banner" />

            <section class="fh-home-block" aria-label="Fandom categories">
                @if($categories->isEmpty())
                    <x-empty title="No categories yet" message="The fandom categories are being set up." icon="bi-collection" />
                @else
                    <div class="fh-cat-row">
                        @foreach($categories as $category)
                            <x-home-category :item="$category" />
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="fh-home-block">
                <div class="fh-home-head">
                    <h2><i class="bi bi-fire" aria-hidden="true"></i> Trending This Week</h2>
                    <a href="{{ route('explore', ['sort' => 'popular']) }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($trending->isEmpty())
                    <x-empty title="Nothing trending yet" message="Published titles will appear here as soon as they land." icon="bi-fire" />
                @else
                    <div class="fh-poster-row">
                        @foreach($trending->take(5) as $item)
                            <x-home-poster :item="$item" />
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="fh-home-block">
                <div class="fh-home-head">
                    <h2><i class="bi bi-stars" aria-hidden="true"></i> Featured Characters</h2>
                    <a href="{{ route('characters.index') }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($featuredCharacters->isEmpty())
                    <x-empty title="No characters yet" message="Character spotlights will show up here once they are added." icon="bi-people" />
                @else
                    <div class="fh-poster-row fh-face-row">
                        @foreach($featuredCharacters->take(5) as $item)
                            <x-home-character :item="$item" />
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="fh-home-block">
                <div class="fh-home-head">
                    <h2><i class="bi bi-newspaper" aria-hidden="true"></i> Latest Articles</h2>
                    <a href="{{ route('articles.index') }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($latestArticles->isEmpty())
                    <x-empty title="No articles yet" message="Editorial drops and guides will land here." icon="bi-newspaper" />
                @else
                    <div class="fh-story-row">
                        @foreach($latestArticles->take(4) as $item)
                            <x-home-article :item="$item" />
                        @endforeach
                    </div>
                @endif
            </section>

            @if($categories->isNotEmpty())
                <section class="fh-home-block">
                    <div class="fh-home-head">
                        <h2><i class="bi bi-grid" aria-hidden="true"></i> Popular Fandoms</h2>
                        <a href="{{ route('explore') }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                    <div class="fh-fandom-row">
                        @foreach($categories->take(4) as $category)
                            <x-home-fandom :item="$category" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if($communityPosts->isNotEmpty())
                <section class="fh-home-block">
                    <div class="fh-home-head">
                        <h2><i class="bi bi-people" aria-hidden="true"></i> From the Community</h2>
                        <a href="{{ route('community.index') }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                    <div class="fh-story-row">
                        @foreach($communityPosts as $post)
                            <x-community-card :item="$post" />
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="fh-home-block">
                <div class="fh-home-head">
                    <h2><i class="bi bi-calendar-event" aria-hidden="true"></i> Upcoming Events</h2>
                    <a href="{{ route('events.index') }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($upcomingEvents->isEmpty())
                    <x-empty title="No upcoming events" message="Conventions and meetups will be listed here." icon="bi-calendar-event" />
                @else
                    <div class="fh-cal-grid">
                        @foreach($upcomingEvents->take(4) as $item)
                            <x-home-calendar :item="$item" />
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="fh-home-block">
                <div class="fh-home-head">
                    <h2><i class="bi bi-clock" aria-hidden="true"></i> Upcoming Releases</h2>
                    <a href="{{ route('releases.index') }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($upcomingReleases->isEmpty())
                    <x-empty title="No upcoming releases" message="Dates will be tracked here." icon="bi-clock-history" />
                @else
                    <div class="fh-drop-row">
                        @foreach($upcomingReleases->take(4) as $item)
                            <x-home-drop :item="$item" />
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="fh-dash-rail" aria-label="Profile, events, releases and help">
            @if($railProfile)
                <section class="fh-rail-card fh-rail-profile">
                    <div class="fh-rail-who">
                        <x-avatar :user="auth()->user()" size="lg" />
                        <div>
                            <strong>{{ $railProfile['name'] }}</strong>
                            <span>Member{{ $railProfile['since'] ? ' · '.$railProfile['since'] : '' }}</span>
                        </div>
                    </div>
                    <dl class="fh-rail-stats">
                        <div>
                            <dt>Bookmarks</dt>
                            <dd>{{ $railProfile['bookmarks'] }}</dd>
                        </div>
                        <div>
                            <dt>Posts</dt>
                            <dd>{{ $railProfile['posts'] }}</dd>
                        </div>
                        <div>
                            <dt>Ratings</dt>
                            <dd>{{ $railProfile['ratings'] }}</dd>
                        </div>
                    </dl>
                    <a class="btn btn-fh btn-sm w-100" href="{{ route('profile.show') }}">View Profile</a>
                </section>
            @else
                <section class="fh-rail-card fh-rail-profile">
                    <strong class="fh-rail-guest-title">Join Fan Hub+</strong>
                    <p>Save bookmarks, rate titles and follow the releases you care about.</p>
                    <a class="btn btn-fh btn-sm w-100" href="{{ route('register') }}">Sign Up Now</a>
                </section>
            @endif

            <section class="fh-rail-card">
                <div class="fh-home-head">
                    <h2>Quick Access</h2>
                </div>
                <nav class="fh-quick" aria-label="Quick access">
                    <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2" aria-hidden="true"></i> My Dashboard</a>
                    <a href="{{ route('bookmarks.index') }}"><i class="bi bi-bookmark" aria-hidden="true"></i> Bookmarks</a>
                    <a href="{{ route('settings.show') }}"><i class="bi bi-sliders" aria-hidden="true"></i> Settings</a>
                    <a href="{{ route('submissions.create') }}"><i class="bi bi-plus-circle" aria-hidden="true"></i> Create Fan Content</a>
                </nav>
            </section>

            <section class="fh-rail-card">
                <div class="fh-home-head">
                    <h2><i class="bi bi-calendar3" aria-hidden="true"></i> Latest Events</h2>
                    <a href="{{ route('events.index') }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($upcomingEvents->isEmpty())
                    <x-empty title="No upcoming events" message="Conventions and meetups will be listed here." icon="bi-calendar-event" />
                @else
                    <div class="fh-rail-list">
                        @foreach($upcomingEvents->take(3) as $item)
                            <x-home-event :item="$item" />
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="fh-rail-card">
                <div class="fh-home-head">
                    <h2><i class="bi bi-star" aria-hidden="true"></i> Latest Releases</h2>
                    <a href="{{ route('releases.index') }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($upcomingReleases->isEmpty())
                    <x-empty title="No upcoming releases" message="Dates will be tracked here." icon="bi-clock-history" />
                @else
                    <div class="fh-rail-list">
                        @foreach($upcomingReleases->take(4) as $item)
                            <x-home-release :item="$item" />
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="fh-rail-card">
                <div class="fh-home-head">
                    <h2><i class="bi bi-bag" aria-hidden="true"></i> Merchandise Showcase</h2>
                    <a href="{{ route('merchandise.index') }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($merchandise->isEmpty())
                    <x-empty title="Showcase is empty" message="Collectibles will be previewed here." icon="bi-bag" />
                @else
                    <div class="fh-merch-row">
                        @foreach($merchandise->take(3) as $item)
                            <x-home-merch :item="$item" />
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="fh-help-card">
                <span class="fh-help-bot" aria-hidden="true"><i class="bi bi-robot"></i></span>
                <div>
                    <h2>Need Help?</h2>
                    <p class="fh-help-kicker">Ask our AI Assistant</p>
                    <p>Get instant answers about your favorite fandoms.</p>
                    <button type="button" class="btn btn-fh btn-sm" data-chat-open>
                        Chat Now <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>
            </section>

            <section class="fh-join-card">
                <h2>Join the Community</h2>
                <p>Share, discuss and connect with fans who are into the same titles.</p>
                @auth
                    <a class="btn btn-fh btn-sm" href="{{ route('submissions.create') }}">Share now</a>
                @else
                    <a class="btn btn-fh btn-sm" href="{{ route('register') }}">Join Now</a>
                @endauth
            </section>
        </aside>
    </div>
@endsection
