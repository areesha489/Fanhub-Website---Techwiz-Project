@extends('layouts.app')

@section('title', 'My profile · Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Dashboard', 'to' => route('dashboard')],
        ['label' => 'Profile'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Account</span>
            <h1 class="fh-page-title">My profile</h1>
            <p class="fh-page-sub">
                Your public details, picture and the fandoms you follow.
            </p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('settings.show') }}">
            <i class="bi bi-sliders" aria-hidden="true"></i> Display settings
        </a>
    </header>

    @if ($errors->any())
        <x-error class="mb-4" title="Check your details" :message="$errors->first()" />
    @endif

    <div class="fh-layout-with-aside fh-aside-right">
        <div>
            {{-- Profile details --}}
            <div class="fh-panel mb-4">
                <p class="fh-panel-title">
                    <i class="bi bi-person-vcard" style="color:#8B5CF6" aria-hidden="true"></i>
                    Profile details
                </p>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="fh-field-row mb-3">
                        <div class="fh-field-group mb-0">
                            <label class="fh-label" for="profile-name">
                                Display name <span class="req" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="profile-name"
                                type="text"
                                name="name"
                                class="fh-field @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                minlength="3"
                                maxlength="60"
                                autocomplete="nickname"
                                required
                                aria-describedby="@error('name') profile-name-error @enderror"
                            >
                            @error('name')
                                <div class="fh-invalid-feedback" id="profile-name-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="fh-field-group mb-0">
                            <label class="fh-label" for="profile-email">
                                Email address <span class="req" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="profile-email"
                                type="email"
                                name="email"
                                class="fh-field @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                autocomplete="email"
                                required
                                aria-describedby="profile-email-help @error('email') profile-email-error @enderror"
                            >
                            @error('email')
                                <div class="fh-invalid-feedback" id="profile-email-error">{{ $message }}</div>
                            @enderror
                            <div class="fh-help" id="profile-email-help">
                                Changing it sends a fresh confirmation link.
                            </div>
                        </div>
                    </div>

                    <div class="fh-field-group">
                        <label class="fh-label" for="profile-bio">
                            Bio
                            <span class="fh-label-hint"><span id="bio-count">{{ mb_strlen((string) old('bio', $user->bio)) }}</span>/500</span>
                        </label>
                        <textarea
                            id="profile-bio"
                            name="bio"
                            class="fh-field @error('bio') is-invalid @enderror"
                            rows="4"
                            maxlength="500"
                            placeholder="What do you geek out about?"
                            aria-describedby="profile-bio-help @error('bio') profile-bio-error @enderror"
                        >{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <div class="fh-invalid-feedback" id="profile-bio-error">{{ $message }}</div>
                        @enderror
                        <div class="fh-help" id="profile-bio-help">
                            Up to 500 characters — a sentence or two about your taste.
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-fh">
                            <i class="bi bi-check2" aria-hidden="true"></i> Save profile
                        </button>
                        @if (! $user->hasVerifiedEmail())
                            <a class="btn btn-fh-ghost" href="{{ route('verification.notice') }}">
                                <i class="bi bi-envelope-check" aria-hidden="true"></i> Verify email
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Favourite fandoms --}}
            <div class="fh-panel">
                <p class="fh-panel-title">
                    <i class="bi bi-heart" style="color:#EC4899" aria-hidden="true"></i>
                    Favourite fandoms
                </p>

                <form method="POST" action="{{ route('profile.favorites') }}">
                    @csrf
                    @method('PUT')

                    <div class="fh-field-group">
                        <span class="fh-label" id="favorites-label">
                            Pick the categories you follow
                            <span class="fh-label-hint">used for your dashboard picks</span>
                        </span>

                        @if ($categories->isEmpty())
                            <x-empty
                                title="No categories yet"
                                message="Once categories are published you can choose your favourites here."
                            />
                        @else
                            <div class="d-flex flex-wrap gap-2" role="group" aria-labelledby="favorites-label">
                                @foreach ($categories as $category)
                                    <label class="fh-chip" for="favorite-{{ $category->id }}">
                                        <input
                                            type="checkbox"
                                            class="form-check-input me-1"
                                            id="favorite-{{ $category->id }}"
                                            name="categories[]"
                                            value="{{ $category->id }}"
                                            @checked(in_array($category->id, $favoriteIds, true))
                                        >
                                        <i class="bi {{ $category->icon ?: 'bi-collection-fill' }}" aria-hidden="true"></i>
                                        {{ $category->name }}
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        @error('categories')
                            <div class="fh-invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('categories.*')
                            <div class="fh-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-fh">
                        <i class="bi bi-heart-fill" aria-hidden="true"></i> Save favourites
                    </button>
                </form>
            </div>
        </div>

        <aside>
            {{-- Avatar --}}
            <div class="fh-panel mb-4 text-center">
                <p class="fh-panel-title justify-content-center">
                    <i class="bi bi-camera" style="color:#22D3EE" aria-hidden="true"></i>
                    Profile picture
                </p>

                <div class="mb-3">
                    <x-avatar :user="$user" size="xl" />
                </div>

                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="fh-field-group text-start">
                        <label class="fh-label" for="profile-avatar">New picture</label>
                        <input
                            id="profile-avatar"
                            type="file"
                            name="avatar"
                            class="fh-field @error('avatar') is-invalid @enderror"
                            accept="image/jpeg,image/png,image/webp"
                            aria-describedby="profile-avatar-help @error('avatar') profile-avatar-error @enderror"
                        >
                        @error('avatar')
                            <div class="fh-invalid-feedback" id="profile-avatar-error">{{ $message }}</div>
                        @enderror
                        <div class="fh-help" id="profile-avatar-help">
                            JPG, PNG or WebP · up to 2 MB.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-fh-ghost w-100">
                        <i class="bi bi-upload" aria-hidden="true"></i> Upload picture
                    </button>
                </form>
            </div>

            {{-- Preferences summary --}}
            <div class="fh-panel">
                <p class="fh-panel-title">
                    <i class="bi bi-sliders" style="color:#34D399" aria-hidden="true"></i>
                    Display preferences
                </p>

                <ul class="list-unstyled mb-3 fh-text-sm">
                    <li class="d-flex justify-content-between py-1 border-bottom border-opacity-25">
                        <span class="fh-text-dim">Theme</span>
                        <span class="fw-semibold text-capitalize">{{ $preferences->theme ?: 'dark' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-bottom border-opacity-25">
                        <span class="fh-text-dim">Font size</span>
                        <span class="fw-semibold">{{ (int) round(((float) $preferences->font_scale) * 100) }}%</span>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-bottom border-opacity-25">
                        <span class="fh-text-dim">Motion</span>
                        <span class="fw-semibold">{{ $preferences->reduced_motion ? 'Reduced' : 'Full' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1">
                        <span class="fh-text-dim">Email digest</span>
                        <span class="fw-semibold">{{ $preferences->newsletter ? 'Weekly' : 'Off' }}</span>
                    </li>
                </ul>

                <a class="btn btn-fh-ghost btn-sm w-100" href="{{ route('settings.show') }}">
                    <i class="bi bi-arrow-right" aria-hidden="true"></i> Change in settings
                </a>
            </div>
        </aside>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var field = document.getElementById('profile-bio');
            var counter = document.getElementById('bio-count');
            if (!field || !counter) return;
            field.addEventListener('input', function () {
                counter.textContent = String(field.value.length);
            });
        })();
    </script>
@endsection
