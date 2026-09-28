@extends('layouts.app')

@section('title', 'Sitemap · Fan Hub+')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Home', 'to' => route('home')],
        ['label' => 'Sitemap'],
    ]" />

    <header class="fh-page-head">
        <div>
            <span class="fh-eyebrow">Directory</span>
            <h1 class="fh-page-title">Sitemap</h1>
            <p class="fh-page-sub">
                Every corner of Fan Hub+ in one list — pages, categories and the newest entries.
            </p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('search') }}">
            <i class="bi bi-search" aria-hidden="true"></i> Search instead
        </a>
    </header>

    @foreach ($groups as $groupName => $links)
        <x-section :title="$groupName" :subtitle="\Illuminate\Support\Str::plural('link', count($links))">
            @if ($links === [])
                <x-empty title="Nothing here yet" message="Links for this section appear once content is published." />
            @else
                <div class="fh-panel mb-4">
                    <ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
                        @foreach ($links as $link)
                            <li>
                                <a class="fh-chip" href="{{ $link['to'] }}">
                                    <i class="bi bi-link-45deg" aria-hidden="true"></i>
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-section>
    @endforeach

    @if ($latest !== [])
        <div class="fh-divider mb-4" aria-hidden="true"></div>

        <header class="fh-page-head">
            <div>
                <h2 class="fh-page-title" style="font-size: clamp(1.25rem, 2.4vw, 1.65rem);">
                    Latest entries
                </h2>
                <p class="fh-page-sub">
                    The most recently published items across the catalogue.
                </p>
            </div>
        </header>

        @foreach ($latest as $groupName => $links)
            <x-section :title="$groupName" :subtitle="\Illuminate\Support\Str::plural('link', count($links))">
                <div class="fh-panel mb-4">
                    @if ($links === [])
                        <x-empty title="Nothing here yet" message="Once something is published in this section it will be listed here." />
                    @else
                        <ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
                            @foreach ($links as $link)
                                <li>
                                    <a class="fh-chip" href="{{ $link['to'] }}">
                                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                        {{ $link['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </x-section>
        @endforeach
    @endif
@endsection
