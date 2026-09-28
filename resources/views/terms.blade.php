@extends('layouts.app')

@section('title', 'Terms of Service · Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Terms'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Legal</span>
            <h1 class="fh-page-title">Terms of Service</h1>
            <p class="fh-page-sub">
                Last updated {{ now()->format('F j, Y') }} — the ground rules for using Fan Hub+.
            </p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('privacy') }}">
            <i class="bi bi-shield-lock" aria-hidden="true"></i> Privacy Policy
        </a>
    </header>

    <div class="fh-panel" style="max-width: 62rem;">
        <section class="mb-4">
            <h2 class="fh-panel-title">1. Accepting these terms</h2>
            <p class="fh-text-sm fh-text-dim">
                By creating an account or browsing Fan Hub+ you agree to these terms and to
                our <a href="{{ route('privacy') }}">Privacy Policy</a>. If you do not agree,
                please do not use the service. You must be at least 13 years old, or the age
                of digital consent in your country, to hold an account.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">2. Your account</h2>
            <p class="fh-text-sm fh-text-dim">
                You are responsible for the activity on your account and for keeping your
                password secret. Provide accurate information, keep your email address current
                so you can recover access, and tell us promptly if you believe your account has
                been compromised. One person should hold one account; automated or bulk
                registrations are not allowed.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">3. Contributing content</h2>
            <p class="fh-text-sm fh-text-dim">
                When you submit artwork, video, cosplay or writing you confirm that it is yours
                or that you have permission to share it, and that it does not infringe anyone
                else's rights. You grant Fan Hub+ a non-exclusive licence to display, review
                and promote that submission as part of the platform. Moderators may approve,
                edit or reject submissions, and may remove content that breaks these terms.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">4. Acceptable use</h2>
            <p class="fh-text-sm fh-text-dim">
                Do not post harassment, hate speech, spam, piracy links or sexually explicit
                material; do not attempt to break, scrape or overload the service; do not
                impersonate others or interfere with another member's account. Ratings,
                bookmarks and feedback must reflect genuine interaction — manipulating them
                may lead to suspension.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">5. Intellectual property</h2>
            <p class="fh-text-sm fh-text-dim">
                The Fan Hub+ name, interface, original artwork and platform code belong to us
                or our licensors. Fan submissions remain the property of their creators.
                Trademarks and characters referenced on the site belong to their respective
                owners; their appearance here is for identification and commentary.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">6. Availability and liability</h2>
            <p class="fh-text-sm fh-text-dim">
                The service is provided "as is". We aim for high availability but cannot
                guarantee uninterrupted access, and we may change or withdraw features. To the
                extent permitted by law, Fan Hub+ is not liable for indirect or consequential
                losses arising from your use of the platform. Nothing here limits liability
                that cannot be limited by law.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="fh-panel-title">7. Suspension</h2>
            <p class="fh-text-sm fh-text-dim">
                We may suspend or close accounts that breach these terms, with notice where the
                situation allows. You can close your account at any time by contacting us
                through the <a href="{{ route('feedback.create') }}">feedback form</a>.
            </p>
        </section>

        <section>
            <h2 class="fh-panel-title">8. Changes and contact</h2>
            <p class="fh-text-sm fh-text-dim mb-0">
                We may update these terms; the date above shows the latest revision, and
                members will be notified of material changes. Questions can be sent to
                <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>
                or via the <a href="{{ route('contact') }}">contact page</a>.
            </p>
        </section>
    </div>
@endsection
