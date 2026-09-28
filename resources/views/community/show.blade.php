@extends('layouts.app')

@section('title', $post->title.' · Fan Hub+')

@section('content')
<x-breadcrumbs :items="[
    ['label' => 'Home', 'to' => route('home')],
    ['label' => 'Community', 'to' => route('community.index')],
    ['label' => $post->title],
]" />

<article class="fh-card fh-card-pad">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <x-badge variant="cyan">{{ \Illuminate\Support\Str::headline($post->type) }}</x-badge>
        @if($post->category)
            <a class="fh-chip" href="{{ route('category.show', $post->category->slug) }}">{{ $post->category->name }}</a>
        @endif
        @if($post->publishedArticle && $post->publishedArticle->status === \App\Models\Article::STATUS_PUBLISHED)
            <a class="fh-chip" href="{{ route('articles.show', $post->publishedArticle->slug) }}">Read in Articles</a>
        @endif
        @if($post->publishedMedia && $post->publishedMedia->status === \App\Models\Media::STATUS_PUBLISHED)
            <a class="fh-chip" href="{{ route('media.show', $post->publishedMedia->slug) }}">Open in Multimedia</a>
        @endif
    </div>

    <h1 class="fh-page-title">{{ $post->title }}</h1>
    <p class="fh-text-dim mb-4">
        @if($post->user)
            {{ $post->user->name }}
            <span aria-hidden="true"> · </span>
        @endif
        {{ ($post->reviewed_at ?? $post->created_at)?->format('M j, Y') }}
    </p>

    <img
        class="fh-img rounded mb-4"
        src="{{ $post->displayImage() }}"
        alt=""
        width="1200"
        height="675"
        decoding="async"
    >

    @foreach(preg_split("/\r\n|\n|\r/", (string) $post->description) ?: [] as $paragraph)
        @if(trim($paragraph) !== '')
            <p class="fh-text-muted-2" style="max-width: 72ch;">{{ $paragraph }}</p>
        @endif
    @endforeach

    @if(filled($post->url) && filter_var($post->url, FILTER_VALIDATE_URL) && in_array(strtolower((string) parse_url($post->url, PHP_URL_SCHEME)), ['http', 'https'], true))
        <p class="mt-3 mb-0">
            <a href="{{ $post->url }}" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                Open the source link
            </a>
        </p>
    @endif
</article>
@endsection
