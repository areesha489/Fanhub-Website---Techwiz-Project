@extends('layouts.app')

@section('title', 'My submissions · Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Dashboard', 'to' => route('dashboard')],
        ['label' => 'My submissions'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Contribute</span>
            <h1 class="fh-page-title">My submissions</h1>
            <p class="fh-page-sub">
                Everything you have sent for review, with the moderator decision and notes.
            </p>
        </div>
        <a class="btn btn-fh" href="{{ route('submissions.create') }}">
            <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> New submission
        </a>
    </header>

    @if ($submissions->isEmpty())
        <x-empty
            title="No submissions yet"
            message="Send artwork, cosplay, video or a write-up and track its review status right here."
            icon="bi-cloud-arrow-up"
        >
            <a class="btn btn-fh btn-sm" href="{{ route('submissions.create') }}">Submit content</a>
        </x-empty>
    @else
        <div class="fh-panel p-0 overflow-hidden mb-4">
            <ul class="list-unstyled mb-0">
                @foreach ($submissions as $submission)
                    @php
                        $statusVariant = [
                            \App\Models\FanSubmission::STATUS_PENDING => 'pending',
                            \App\Models\FanSubmission::STATUS_APPROVED => 'approved',
                            \App\Models\FanSubmission::STATUS_REJECTED => 'rejected',
                        ][$submission->status] ?? 'pending';
                    @endphp

                    <li class="d-flex gap-3 p-3 {{ $loop->last ? '' : 'border-bottom' }} border-opacity-25 flex-wrap">
                        <img
                            class="fh-img rounded"
                            src="{{ img($submission->image, $submission->title, 240, 180, 'artwork') }}"
                            alt=""
                            width="96"
                            height="72"
                            loading="lazy"
                            decoding="async"
                            style="width:96px;height:72px;object-fit:cover;flex:0 0 auto;"
                        >

                        <div class="flex-grow-1" style="min-width: 16rem;">
                            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                                <h2 class="fh-card-title mb-1" style="font-size: 1rem;">
                                    {{ $submission->title }}
                                </h2>
                                <span class="fh-status fh-status-{{ $statusVariant }}">
                                    {{ str_replace('_', ' ', $submission->status) }}
                                </span>
                            </div>

                            <div class="fh-text-sm fh-text-dim mb-2">
                                <x-badge variant="cyan">{{ \Illuminate\Support\Str::headline($submission->type) }}</x-badge>
                                @if ($submission->category)
                                    <span class="mx-1" aria-hidden="true">·</span>
                                    {{ $submission->category->name }}
                                @endif
                                <span class="mx-1" aria-hidden="true">·</span>
                                sent {{ $submission->created_at?->format('M j, Y') }}
                                @if ($submission->reviewed_at)
                                    <span class="mx-1" aria-hidden="true">·</span>
                                    reviewed {{ $submission->reviewed_at->format('M j, Y') }}
                                @endif
                            </div>

                            <p class="fh-text-sm fh-text-dim fh-clamp-3 mb-2">
                                {{ $submission->description }}
                            </p>

                            @if ($submission->status === \App\Models\FanSubmission::STATUS_APPROVED && filled($submission->slug))
                                <a class="fh-text-sm" href="{{ route('community.show', $submission->slug) }}">
                                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                    View on the site
                                </a>
                            @endif

                            @if ($submission->url)
                                <a class="fh-text-sm" href="{{ $submission->url }}" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                    Open the source link
                                </a>
                            @endif

                            @if (filled($submission->admin_note ?? null) || filled($submission->admin_response ?? null))
                                <div class="fh-help mt-2">
                                    <i class="bi bi-reply" aria-hidden="true"></i>
                                    {{ $submission->admin_note ?? $submission->admin_response }}
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <x-pagination :paginator="$submissions" />
    @endif
@endsection
