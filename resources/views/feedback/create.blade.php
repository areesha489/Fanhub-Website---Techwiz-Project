@extends('layouts.app')

@section('title', 'Feedback · Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Feedback'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Talk to us</span>
            <h1 class="fh-page-title">Feedback &amp; support</h1>
            <p class="fh-page-sub">
                Bugs, ideas or a question about Fan Hub+ — every message reaches a human.
            </p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('contact') }}">
            <i class="bi bi-envelope" aria-hidden="true"></i> Other ways to reach us
        </a>
    </header>

    @if ($ticket !== null)
        <div class="fh-panel mb-4" role="status">
            <div class="d-flex align-items-start gap-3">
                <span class="fh-icon-tile" style="background:linear-gradient(135deg,#34D399,#22D3EE);" aria-hidden="true">
                    <i class="bi bi-check2-circle"></i>
                </span>
                <div>
                    <p class="fh-panel-title mb-1">
                        Thanks — your message is in!
                    </p>
                    <p class="fh-text-sm fh-text-dim mb-2">
                        Ticket <span class="fh-badge fh-badge-violet rounded-pill">#{{ $ticket->id }}</span>
                        · we usually reply within two working days. Keep this id if you follow up.
                    </p>
                    <a class="btn btn-fh-ghost btn-sm" href="#my-feedback">
                        <i class="bi bi-journal-text" aria-hidden="true"></i> See my tickets
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="fh-layout-with-aside fh-aside-right">
        <div class="fh-panel">
            <p class="fh-panel-title">
                <i class="bi bi-chat-square-text" style="color:#8B5CF6" aria-hidden="true"></i>
                Send a message
            </p>

            @if ($errors->any())
                <x-error class="mb-4" title="Check your details" :message="$errors->first()" />
            @endif

            <form method="POST" action="{{ route('feedback.store') }}">
                @csrf

                <div class="fh-field-row mb-3">
                    <div class="fh-field-group mb-0">
                        <label class="fh-label" for="feedback-type">
                            What is this about? <span class="req" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="feedback-type"
                            name="type"
                            class="fh-field @error('type') is-invalid @enderror"
                            required
                            aria-describedby="@error('type') feedback-type-error @enderror"
                        >
                            @foreach ($types as $feedbackType)
                                <option value="{{ $feedbackType }}" @selected(old('type', 'bug') === $feedbackType)>
                                    {{ \Illuminate\Support\Str::headline($feedbackType) }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="fh-invalid-feedback" id="feedback-type-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="fh-field-group mb-0">
                        <label class="fh-label" for="feedback-subject">
                            Subject <span class="req" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="feedback-subject"
                            type="text"
                            name="subject"
                            class="fh-field @error('subject') is-invalid @enderror"
                            value="{{ old('subject') }}"
                            minlength="5"
                            maxlength="255"
                            placeholder="Short summary"
                            autofocus
                            required
                            aria-describedby="@error('subject') feedback-subject-error @enderror"
                        >
                        @error('subject')
                            <div class="fh-invalid-feedback" id="feedback-subject-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="fh-field-group">
                    <label class="fh-label" for="feedback-message">
                        Message <span class="req" aria-hidden="true">*</span>
                        <span class="fh-label-hint"><span id="feedback-count">{{ mb_strlen((string) old('message')) }}</span>/5000</span>
                    </label>
                    <textarea
                        id="feedback-message"
                        name="message"
                        class="fh-field @error('message') is-invalid @enderror"
                        rows="7"
                        minlength="20"
                        maxlength="5000"
                        placeholder="Tell us what happened, what you expected and what would make it better."
                        required
                        aria-describedby="feedback-message-help @error('message') feedback-message-error @enderror"
                    >{{ old('message') }}</textarea>
                    @error('message')
                        <div class="fh-invalid-feedback" id="feedback-message-error">{{ $message }}</div>
                    @enderror
                    <div class="fh-help" id="feedback-message-help">
                        At least 20 characters — enough detail for us to act on it.
                    </div>
                </div>

                <div class="fh-field-group">
                    <label class="fh-label" for="feedback-email">
                        Reply address
                        @unless (auth()->check())
                            <span class="req" aria-hidden="true">*</span>
                        @endunless
                    </label>
                    <input
                        id="feedback-email"
                        type="email"
                        name="email"
                        class="fh-field @error('email') is-invalid @enderror"
                        value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}"
                        placeholder="you@example.com"
                        autocomplete="email"
                        @unless (auth()->check()) required @endunless
                        aria-describedby="feedback-email-help @error('email') feedback-email-error @enderror"
                    >
                    @error('email')
                        <div class="fh-invalid-feedback" id="feedback-email-error">{{ $message }}</div>
                    @enderror
                    <div class="fh-help" id="feedback-email-help">
                        Optional when you are signed in — we reply in-app either way.
                    </div>
                </div>

                <button type="submit" class="btn btn-fh">
                    <i class="bi bi-send" aria-hidden="true"></i> Send message
                </button>
            </form>
        </div>

        <aside id="my-feedback">
            <div class="fh-panel">
                <p class="fh-panel-title">
                    <i class="bi bi-journal-check" style="color:#22D3EE" aria-hidden="true"></i>
                    My feedback
                </p>

                @guest
                    <x-empty
                        title="Sign in to track tickets"
                        message="Create an account and every message you send is tracked here with its status."
                        icon="bi-person"
                    >
                        <a class="btn btn-fh-ghost btn-sm" href="{{ route('login') }}">Sign in</a>
                    </x-empty>
                @else
                    @if ($myFeedback->isEmpty())
                        <x-empty
                            title="No tickets yet"
                            message="Anything you send from this form appears here with its status and our reply."
                            icon="bi-inbox"
                        />
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach ($myFeedback as $item)
                                <li class="py-2 {{ $loop->last ? '' : 'border-bottom' }} border-opacity-25">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="min-w-0">
                                            <span class="fw-semibold fh-text-sm fh-clamp-2">{{ $item->subject }}</span>
                                            <div class="fh-text-sm fh-text-dim">
                                                #{{ $item->id }} · {{ \Illuminate\Support\Str::headline($item->type) }}
                                                · {{ $item->created_at?->format('M j, Y') }}
                                            </div>
                                        </div>
                                        <span class="fh-status fh-status-{{ $item->status }}">
                                            {{ str_replace('_', ' ', $item->status) }}
                                        </span>
                                    </div>

                                    @if (filled($item->admin_response))
                                        <div class="fh-help mt-2">
                                            <i class="bi bi-reply" aria-hidden="true"></i>
                                            {{ $item->admin_response }}
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endguest
            </div>
        </aside>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var field = document.getElementById('feedback-message');
            var counter = document.getElementById('feedback-count');
            if (!field || !counter) return;
            field.addEventListener('input', function () {
                counter.textContent = String(field.value.length);
            });
        })();
    </script>
@endsection
