@extends('layouts.app')

@section('title', 'Settings · Fan Hub+')

@section('content')
    @php
        $fontScale = (float) ($preferences->font_scale ?: 1);
        $fontScaleKey = in_array($fontScale, [0.9, 1.0, 1.1, 1.25], true) ? $fontScale : 1.0;
        $fontScaleAttr = rtrim(rtrim(number_format($fontScaleKey, 2, '.', ''), '0'), '.');
        $currentTheme = ($preferences->theme ?? '') === 'light' ? 'light' : 'dark';
        $currentMotion = $preferences->reduced_motion ? 'reduced' : 'full';
    @endphp

    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Dashboard', 'to' => route('dashboard')],
        ['label' => 'Settings'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Preferences</span>
            <h1 class="fh-page-title">Settings</h1>
            <p class="fh-page-sub">
                Profile, appearance, accessibility, security and what we send you.
            </p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('profile.show') }}">
            <i class="bi bi-person" aria-hidden="true"></i> Full profile
        </a>
    </header>

    @if ($errors->any())
        <x-error class="mb-4" title="Check your details" :message="$errors->first()" />
    @endif

    <ul class="nav nav-tabs" role="tablist" aria-label="Settings sections">
        @foreach ($tabs as $tabName)
            <li class="nav-item" role="presentation">
                <button
                    type="button"
                    class="nav-link @if ($tab === $tabName) active @endif"
                    id="tab-{{ $tabName }}-button"
                    data-bs-toggle="tab"
                    data-bs-target="#pane-{{ $tabName }}"
                    type="button"
                    role="tab"
                    aria-controls="pane-{{ $tabName }}"
                    aria-selected="{{ $tab === $tabName ? 'true' : 'false' }}"
                >
                    @if ($tabName === 'profile') <i class="bi bi-person me-1" aria-hidden="true"></i> Profile
                    @elseif ($tabName === 'preferences') <i class="bi bi-stars me-1" aria-hidden="true"></i> Preferences
                    @elseif ($tabName === 'accessibility') <i class="bi bi-universal-access me-1" aria-hidden="true"></i> Accessibility
                    @elseif ($tabName === 'security') <i class="bi bi-shield-lock me-1" aria-hidden="true"></i> Security
                    @else <i class="bi bi-bell me-1" aria-hidden="true"></i> Notifications
                    @endif
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content pt-4">
        {{-- Profile --}}
        <div class="tab-pane fade @if ($tab === 'profile') show active @endif" id="pane-profile" role="tabpanel" aria-labelledby="tab-profile-button" tabindex="0">
            <div class="fh-panel" style="max-width: 46rem;">
                <p class="fh-panel-title">
                    <i class="bi bi-person-vcard" style="color:#8B5CF6" aria-hidden="true"></i>
                    Profile details
                </p>

                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="scope" value="profile">

                    <div class="fh-field-row mb-3">
                        <div class="fh-field-group mb-0">
                            <label class="fh-label" for="settings-name">
                                Display name <span class="req" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="settings-name"
                                type="text"
                                name="name"
                                class="fh-field @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                minlength="3"
                                maxlength="60"
                                autocomplete="nickname"
                                required
                                aria-describedby="@error('name') settings-name-error @enderror"
                            >
                            @error('name')
                                <div class="fh-invalid-feedback" id="settings-name-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="fh-field-group mb-0">
                            <label class="fh-label" for="settings-email">
                                Email address <span class="req" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="settings-email"
                                type="email"
                                name="email"
                                class="fh-field @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                autocomplete="email"
                                required
                                aria-describedby="settings-email-help @error('email') settings-email-error @enderror"
                            >
                            @error('email')
                                <div class="fh-invalid-feedback" id="settings-email-error">{{ $message }}</div>
                            @enderror
                            <div class="fh-help" id="settings-email-help">
                                Changing this re-sends a confirmation link.
                            </div>
                        </div>
                    </div>

                    <div class="fh-field-group">
                        <label class="fh-label" for="settings-bio">
                            Bio
                            <span class="fh-label-hint"><span id="settings-bio-count">{{ mb_strlen((string) old('bio', $user->bio)) }}</span>/500</span>
                        </label>
                        <textarea
                            id="settings-bio"
                            name="bio"
                            class="fh-field @error('bio') is-invalid @enderror"
                            rows="3"
                            maxlength="500"
                            placeholder="What do you geek out about?"
                        >{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <div class="fh-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-fh">
                        <i class="bi bi-check2" aria-hidden="true"></i> Save profile
                    </button>
                </form>
            </div>
        </div>

        {{-- Preferences --}}
        <div class="tab-pane fade @if ($tab === 'preferences') show active @endif" id="pane-preferences" role="tabpanel" aria-labelledby="tab-preferences-button" tabindex="0">
            <div class="fh-panel" style="max-width: 46rem;">
                <p class="fh-panel-title">
                    <i class="bi bi-stars" style="color:#22D3EE" aria-hidden="true"></i>
                    What we send you
                </p>

                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="scope" value="preferences">

                    <div class="fh-field-group">
                        <label class="fh-label" for="settings-digest">Email digest</label>
                        <select id="settings-digest" class="fh-field @error('email_notifications') is-invalid @enderror" name="email_notifications">
                            <option value="1" @selected((bool) $preferences->email_notifications)>On — activity and release digests</option>
                            <option value="0" @selected(! $preferences->email_notifications)>Off — no digest emails</option>
                        </select>
                        @error('email_notifications')
                            <div class="fh-invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="fh-help">
                            Digests summarise new content in your favourite fandoms. Per-topic
                            choices live under <a href="#pane-notifications" data-bs-toggle="tab" data-bs-target="#pane-notifications">Notifications</a>.
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-fh">
                            <i class="bi bi-check2" aria-hidden="true"></i> Save preferences
                        </button>
                        <a class="btn btn-fh-ghost" href="{{ route('settings.show', ['tab' => 'accessibility']) }}">
                            <i class="bi bi-universal-access" aria-hidden="true"></i> Appearance
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Accessibility --}}
        <div class="tab-pane fade @if ($tab === 'accessibility') show active @endif" id="pane-accessibility" role="tabpanel" aria-labelledby="tab-accessibility-button" tabindex="0">
            <div class="fh-panel" style="max-width: 46rem;">
                <p class="fh-panel-title">
                    <i class="bi bi-universal-access" style="color:#34D399" aria-hidden="true"></i>
                    Theme, text size and motion
                </p>

                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="scope" value="accessibility">

                    <fieldset class="fh-field-group border-0 p-0 m-0">
                        <legend class="fh-label p-0 mb-2">Theme</legend>
                        <div class="d-flex flex-wrap gap-3">
                            <label class="d-flex align-items-center gap-2 fh-label mb-0" for="theme-dark">
                                <input
                                    type="radio"
                                    class="form-check-input mt-0"
                                    id="theme-dark"
                                    name="theme"
                                    value="dark"
                                    data-fh-theme
                                    @checked($currentTheme === 'dark')
                                >
                                Dark (default)
                            </label>
                            <label class="d-flex align-items-center gap-2 fh-label mb-0" for="theme-light">
                                <input
                                    type="radio"
                                    class="form-check-input mt-0"
                                    id="theme-light"
                                    name="theme"
                                    value="light"
                                    data-fh-theme
                                    @checked($currentTheme === 'light')
                                >
                                Light
                            </label>
                        </div>
                        @error('theme')
                            <div class="fh-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </fieldset>

                    <fieldset class="fh-field-group border-0 p-0 m-0 mt-3">
                        <legend class="fh-label p-0 mb-2">Text size</legend>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ([['value' => '0.9', 'label' => '90%'], ['value' => '1', 'label' => '100%'], ['value' => '1.1', 'label' => '110%'], ['value' => '1.25', 'label' => '125%']] as $fontOption)
                                <label class="d-flex align-items-center gap-2 fh-label mb-0" for="font-scale-{{ $fontOption['value'] }}">
                                    <input
                                        type="radio"
                                        class="form-check-input mt-0"
                                        id="font-scale-{{ $fontOption['value'] }}"
                                        name="font_scale"
                                        value="{{ $fontOption['value'] }}"
                                        data-setting="font-scale"
                                        @checked((string) $fontScaleKey === $fontOption['value'])
                                    >
                                    {{ $fontOption['label'] }}
                                </label>
                            @endforeach
                        </div>
                        @error('font_scale')
                            <div class="fh-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </fieldset>

                    <div class="fh-field-group mt-3">
                        <input type="hidden" name="reduced_motion" value="0">
                        <label class="d-flex align-items-center gap-2 fh-label mb-0" for="settings-motion">
                            <input
                                type="checkbox"
                                class="form-check-input mt-0"
                                id="settings-motion"
                                name="reduced_motion"
                                value="1"
                                data-setting="reduced-motion"
                                @checked($preferences->reduced_motion)
                            >
                            Reduce motion — calm transitions and no auto-advancing carousels
                        </label>
                        @error('reduced_motion')
                            <div class="fh-invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="fh-help">
                            Applied instantly and stored with your account.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-fh">
                        <i class="bi bi-check2" aria-hidden="true"></i> Save accessibility
                    </button>
                </form>
            </div>
        </div>

        {{-- Security --}}
        <div class="tab-pane fade @if ($tab === 'security') show active @endif" id="pane-security" role="tabpanel" aria-labelledby="tab-security-button" tabindex="0">
            <div class="fh-panel" style="max-width: 46rem;">
                <p class="fh-panel-title">
                    <i class="bi bi-shield-lock" style="color:#FBBF24" aria-hidden="true"></i>
                    Change password
                </p>

                <form method="POST" action="{{ route('settings.security') }}">
                    @csrf
                    @method('PUT')

                    <div class="fh-field-group">
                        <label class="fh-label" for="current-password">
                            Current password <span class="req" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="current-password"
                            type="password"
                            name="current_password"
                            class="fh-field @error('current_password') is-invalid @enderror"
                            autocomplete="current-password"
                            required
                            autofocus
                            aria-describedby="@error('current_password') current-password-error @enderror"
                        >
                        @error('current_password')
                            <div class="fh-invalid-feedback" id="current-password-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="fh-field-row mb-3">
                        <div class="fh-field-group mb-0">
                            <label class="fh-label" for="new-password">
                                New password <span class="req" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="new-password"
                                type="password"
                                name="password"
                                class="fh-field @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                                required
                                aria-describedby="new-password-help @error('password') new-password-error @enderror"
                            >
                            @error('password')
                                <div class="fh-invalid-feedback" id="new-password-error">{{ $message }}</div>
                            @enderror
                            <div class="fh-help" id="new-password-help">
                                8+ characters with a letter and a number.
                            </div>
                        </div>

                        <div class="fh-field-group mb-0">
                            <label class="fh-label" for="confirm-password">
                                Confirm new password <span class="req" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="confirm-password"
                                type="password"
                                name="password_confirmation"
                                class="fh-field"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-fh">
                        <i class="bi bi-key" aria-hidden="true"></i> Update password
                    </button>

                    <p class="fh-help mt-3 mb-0">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        You stay signed in on this device after the change.
                    </p>
                </form>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="tab-pane fade @if ($tab === 'notifications') show active @endif" id="pane-notifications" role="tabpanel" aria-labelledby="tab-notifications-button" tabindex="0">
            <div class="fh-panel" style="max-width: 46rem;">
                <p class="fh-panel-title">
                    <i class="bi bi-bell" style="color:#EC4899" aria-hidden="true"></i>
                    Email and in-app notifications
                </p>

                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="scope" value="notifications">

                    @foreach ([
                        'email_notifications' => ['Activity digest', 'A short summary of what happened in your fandoms.'],
                        'content_notifications' => ['New content', 'Fresh articles, characters and multimedia in your categories.'],
                        'event_notifications' => ['Events near you', 'Conventions, screenings and meetups worth the trip.'],
                        'newsletter' => ['Newsletter', 'A monthly round-up of everything on Fan Hub+.'],
                    ] as $notificationKey => [$notificationLabel, $notificationHelp])
                        <div class="fh-field-group">
                            <input type="hidden" name="{{ $notificationKey }}" value="0">
                            <label class="d-flex align-items-start gap-2 fh-label mb-0" for="notify-{{ $notificationKey }}">
                                <input
                                    type="checkbox"
                                    class="form-check-input mt-1"
                                    id="notify-{{ $notificationKey }}"
                                    name="{{ $notificationKey }}"
                                    value="1"
                                    @checked((bool) data_get($preferences, $notificationKey))
                                >
                                <span>
                                    {{ $notificationLabel }}
                                    <span class="fh-label-hint d-block">{{ $notificationHelp }}</span>
                                </span>
                            </label>
                            @error($notificationKey)
                                <div class="fh-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-fh">
                        <i class="bi bi-check2" aria-hidden="true"></i> Save notifications
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var html = document.documentElement;
            html.dataset.theme = @json($currentTheme);
            html.dataset.fontScale = @json($fontScaleAttr);
            html.dataset.motion = @json($currentMotion);
        })();
    </script>

    <script>
        (function () {
            var radios = document.querySelectorAll('input[data-fh-theme]');
            Array.prototype.forEach.call(radios, function (radio) {
                radio.addEventListener('change', function () {
                    if (!window.FanHub || !FanHub.applySetting) return;
                    FanHub.applySetting('theme', radio.value);
                    FanHub.post(FanHub.urls.settings, { theme: radio.value, scope: 'accessibility' }, 'PUT')
                        .then(function (res) {
                            if (res.ok) {
                                FanHub.toast('success', 'Theme updated.');
                            } else {
                                FanHub.toast('error', 'The theme could not be saved.');
                            }
                        });
                });
            });
        })();
    </script>

    <script>
        (function () {
            var field = document.getElementById('settings-bio');
            var counter = document.getElementById('settings-bio-count');
            if (!field || !counter) return;
            field.addEventListener('input', function () {
                counter.textContent = String(field.value.length);
            });
        })();
    </script>
@endsection
