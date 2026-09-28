@extends('layouts.app')

@section('title', 'Community · Fan Hub+')

@section('styles')
<style>
    .fh-chip.is-active {
        background: rgba(34, 211, 238, .18);
        border-color: rgba(34, 211, 238, .45);
        color: #67e8f9;
    }
</style>
@endsection

@section('content')
<x-breadcrumbs :items="[['label' => 'Home', 'to' => route('home')], ['label' => 'Community']]" />

<x-page-banner
    kicker="Fan posts"
    title="Community"
    subtitle="Artwork, cosplay, video and write-ups from members, published after a moderator approves them."
    image="{{ asset('images/catalogue/banner-articles.png') }}"
>
    <span class="fh-badge fh-badge-cyan">{{ number_format($posts->total()) }} live</span>
</x-page-banner>

<div class="fh-chip-row" style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.35rem;">
    <a class="fh-chip {{ $type === '' ? 'is-active' : '' }}" href="{{ route('community.index') }}">All</a>
    @foreach($types as $typeOption)
        <a class="fh-chip {{ $type === $typeOption ? 'is-active' : '' }}" href="{{ route('community.index', ['type' => $typeOption]) }}">
            {{ \Illuminate\Support\Str::headline($typeOption) }}
        </a>
    @endforeach
</div>

@if($posts->isEmpty())
    <x-empty
        title="Nothing published yet"
        message="Approved member posts will show up here."
        icon="bi-people"
    />
@else
    <div class="fh-story-row">
        @foreach($posts as $post)
            <x-community-card :item="$post" />
        @endforeach
    </div>

    <x-pagination :paginator="$posts" />
@endif
@endsection
