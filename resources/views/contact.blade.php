@extends('layouts.app')

@section('title', 'Contact · Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Contact'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Get in touch</span>
            <h1 class="fh-page-title">Contact Fan Hub+</h1>
            <p class="fh-page-sub">
                Questions about the platform, a partnership idea or a problem you hit —
                pick the fastest route below.
            </p>
        </div>
        <a class="btn btn-fh" href="{{ route('feedback.create') }}">
            <i class="bi bi-chat-square-text" aria-hidden="true"></i> Open the feedback form
        </a>
    </header>

    <div class="fh-grid mb-4">
        <div class="fh-panel">
            <p class="fh-panel-title">
                <i class="bi bi-envelope-paper" style="color:#8B5CF6" aria-hidden="true"></i>
                Email
            </p>
            <p class="fh-text-sm fh-text-dim mb-2">
                Write to <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
                Include your account email if it is about your profile or a submission.
            </p>
            <p class="fh-help mb-0">
                Typical reply: within two working days.
            </p>
        </div>

        <div class="fh-panel">
            <p class="fh-panel-title">
                <i class="bi bi-bug" style="color:#22D3EE" aria-hidden="true"></i>
                Bugs &amp; suggestions
            </p>
            <p class="fh-text-sm fh-text-dim mb-2">
                The feedback form is the quickest way to reach the team — every message gets a
                ticket id and a status you can follow.
            </p>
            <a class="btn btn-fh-ghost btn-sm" href="{{ route('feedback.create') }}">
                <i class="bi bi-arrow-right" aria-hidden="true"></i> Send feedback
            </a>
        </div>

        <div class="fh-panel">
            <p class="fh-panel-title">
                <i class="bi bi-cloud-arrow-up" style="color:#34D399" aria-hidden="true"></i>
                Content &amp; submissions
            </p>
            <p class="fh-text-sm fh-text-dim mb-2">
                Sending artwork, cosplay, video or an article? Use the submission flow so
                moderators can review it with full context.
            </p>
            <a class="btn btn-fh-ghost btn-sm" href="{{ route('submissions.create') }}">
                <i class="bi bi-arrow-right" aria-hidden="true"></i> Submit content
            </a>
        </div>

        <div class="fh-panel">
            <p class="fh-panel-title">
                <i class="bi bi-shield-check" style="color:#FBBF24" aria-hidden="true"></i>
                Accounts &amp; privacy
            </p>
            <p class="fh-text-sm fh-text-dim mb-2">
                For password resets use <a href="{{ route('password.request') }}">Forgot password</a>.
                Data requests are covered in our <a href="{{ route('privacy') }}">Privacy Policy</a>.
            </p>
            <a class="btn btn-fh-ghost btn-sm" href="{{ route('privacy') }}">
                <i class="bi bi-arrow-right" aria-hidden="true"></i> Read the policy
            </a>
        </div>
    </div>

    <div class="fh-panel">
        <p class="fh-panel-title">
            <i class="bi bi-people" style="color:#EC4899" aria-hidden="true"></i>
            Community channels
        </p>
        <p class="fh-text-sm fh-text-dim">
            Join the conversation on our socials for release news, event reminders and fan art
            drops. Moderation questions are best sent through the feedback form so they are
            tracked properly.
        </p>
        <div class="fh-social mt-3">
            <a href="#" aria-label="Fan Hub+ on X (Twitter)" rel="noopener"><i class="bi bi-twitter-x"></i></a>
            <a href="#" aria-label="Fan Hub+ on YouTube" rel="noopener"><i class="bi bi-youtube"></i></a>
            <a href="#" aria-label="Fan Hub+ on Discord" rel="noopener"><i class="bi bi-discord"></i></a>
            <a href="#" aria-label="Fan Hub+ on Instagram" rel="noopener"><i class="bi bi-instagram"></i></a>
            <a href="#" aria-label="Fan Hub+ on TikTok" rel="noopener"><i class="bi bi-tiktok"></i></a>
        </div>
    </div>
@endsection
