@extends('layouts.app')

@section('title', 'Privacy Policy · Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Privacy'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Legal</span>
            <h1 class="fh-page-title">Privacy Policy</h1>
            <p class="fh-page-sub">
                Last updated {{ now()->format('F j, Y') }} — what we collect, why we collect it,
                and the choices you have.
            </p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('terms') }}">
            <i class="bi bi-file-text" aria-hidden="true"></i> Terms of Service
        </a>
    </header>

    <div class="fh-panel" style="max-width: 62rem;">
        <section class="mb-4">
            <h2 class="fh-panel-title">What we collect</h2>
            <p class="fh-text-sm fh-text-dim">
                When you create an account we store your display name, email address and a
                password that is irreversibly hashed — we can never read it. Optionally you can
                add a bio, a profile picture and the fandoms you follow. While you browse we
                record practical details: bookmarks, ratings, viewed pages, the notifications
                you have read and messages you send through the feedback or chat forms. Basic
                technical data such as device type and approximate location may be logged to
                keep the service reliable.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">How we use it</h2>
            <p class="fh-text-sm fh-text-dim">
                Your data keeps the platform working: it personalises your dashboard, restores
                your bookmarks and preferences, protects your account, and lets moderators
                review the content you submit. We send transactional email — password resets,
                address confirmation and the notifications you switch on — and nothing else
                unless you opt in. We do not sell your personal information.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">Cookies and local storage</h2>
            <p class="fh-text-sm fh-text-dim">
                Fan Hub+ uses session cookies to keep you signed in and a small amount of local
                storage to remember interface choices such as theme and text size. Disabling
                cookies in your browser will sign you out whenever you close the tab.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">Who can see it</h2>
            <p class="fh-text-sm fh-text-dim">
                Your display name and public contributions are visible to other members.
                Admins and moderators can access submissions, feedback and account status in
                order to run the platform — including the email address they need to reply to
                you. We only disclose data when required by law or to prevent abuse.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">Retention and security</h2>
            <p class="fh-text-sm fh-text-dim">
                We keep account data while your account exists, and activity logs for a limited
                period needed for security and analytics. Data is transmitted over HTTPS and
                stored with access limited to the systems that need it. No method is perfectly
                secure, so we work to keep protections proportionate to the risk.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">Your rights</h2>
            <p class="fh-text-sm fh-text-dim">
                You can review and edit your profile and notification choices at any time in
                <a href="{{ route('settings.show') }}">Settings</a> and
                <a href="{{ route('profile.show') }}">Profile</a>. You may request a copy of
                your data or ask us to delete your account by using the
                <a href="{{ route('feedback.create') }}">feedback form</a> — we will confirm
                before anything is removed.
            </p>
        </section>

        <section>
            <h2 class="fh-panel-title">Questions</h2>
            <p class="fh-text-sm fh-text-dim mb-0">
                Contact us at <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>
                or through the <a href="{{ route('contact') }}">contact page</a>. If this policy
                changes materially we will update the date above and, for members, post a
                notification.
            </p>
        </section>
    </div>
@endsection
