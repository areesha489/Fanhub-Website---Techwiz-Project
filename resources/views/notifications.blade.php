@extends('layouts.app')

@section('title', 'Notifications · Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Dashboard', 'to' => route('dashboard')],
        ['label' => 'Notifications'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Inbox</span>
            <h1 class="fh-page-title">Notifications</h1>
            <p class="fh-page-sub">
                @if ($unreadCount > 0)
                    {{ $unreadCount }} {{ \Illuminate\Support\Str::plural('notification', $unreadCount) }} still unread.
                @else
                    You are all caught up.
                @endif
            </p>
        </div>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="btn btn-fh-ghost">
                    <i class="bi bi-check2-all" aria-hidden="true"></i> Mark all as read
                </button>
            </form>
        @endif
    </header>

    @if ($notifications->isEmpty())
        <x-empty
            title="No notifications yet"
            message="Submission decisions, moderation updates and community news will appear here."
            icon="bi-bell"
        >
            <a class="btn btn-fh-ghost btn-sm" href="{{ route('explore') }}">Explore the catalogue</a>
        </x-empty>
    @else
        <div class="fh-panel p-0 overflow-hidden mb-4">
            <ul class="list-unstyled mb-0">
                @foreach ($notifications as $notification)
                    <li
                        class="d-flex gap-3 p-3 {{ $loop->last ? '' : 'border-bottom' }} border-opacity-25"
                        style="{{ $notification->isRead() ? '' : 'background: rgba(139, 92, 246, 0.07);' }}"
                    >
                        <span
                            class="fh-icon-tile"
                            style="background: linear-gradient(135deg, rgba(139,92,246,.3), rgba(236,72,153,.22));"
                            aria-hidden="true"
                        >
                            <i class="bi {{ $notification->isRead() ? 'bi-bell' : 'bi-bell-fill' }}"></i>
                        </span>

                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                                <span class="fw-semibold {{ $notification->isRead() ? 'fh-text-dim' : '' }}">
                                    {{ $notification->title }}
                                </span>
                                <span class="fh-text-sm fh-text-dim">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </span>
                            </div>

                            @if (filled($notification->body))
                                <p class="fh-text-sm fh-text-dim mb-2">
                                    {{ $notification->body }}
                                </p>
                            @endif

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @unless ($notification->isRead())
                                    <x-badge variant="violet">New</x-badge>
                                @endunless

                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf

                                    @if (filled($notification->link))
                                        <button type="submit" class="btn btn-fh-ghost btn-sm">
                                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                            Open
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-fh-ghost btn-sm">
                                            <i class="bi bi-check2" aria-hidden="true"></i>
                                            Mark as read
                                        </button>
                                    @endif
                                </form>

                                @if ($notification->isRead())
                                    <span class="fh-text-sm fh-text-dim">
                                        <i class="bi bi-check2-all" aria-hidden="true"></i> Read
                                    </span>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <x-pagination :paginator="$notifications" />
    @endif
@endsection
