{{--
    AJAX bookmark toggle — markup is a contract with assets/js/app.js (§9).
      data-bookmark   required: click delegation
      data-type       bookmarkable_type: content|article|character|media|event|merchandise|release
      data-id         model id
      data-url        POST /bookmarks (create)
      data-update     PUT/DELETE /bookmarks/{id} (note edit / remove)
      data-guest="1"  guest → JS redirects to /login?next=…
--}}
@props([
    'type',
    'id',
    'bookmarked' => false,
    'note' => null,
    'size' => null,
    'bookmarkId' => null,
])

@php
    $bookmarkGuest = ! auth()->check();
    $bookmarkActive = $bookmarkGuest ? false : (bool) $bookmarked;
    $bookmarkSizeClass = in_array($size, ['sm', 'lg'], true) ? ' fh-bookmark-'.$size : '';
    $bookmarkUpdateUrl = $bookmarkId ? route('bookmarks.update', $bookmarkId) : '';
@endphp

<span {{ $attributes->merge(['class' => 'fh-bookmark-group']) }}>
    <button
        type="button"
        class="fh-bookmark{{ $bookmarkActive ? ' is-active' : '' }}{{ $bookmarkSizeClass }}"
        data-bookmark
        data-type="{{ $type }}"
        data-id="{{ $id }}"
        data-url="{{ route('bookmarks.store') }}"
        data-update="{{ $bookmarkUpdateUrl }}"
        @if($bookmarkId) data-bookmark-id="{{ $bookmarkId }}" @endif
        @if($bookmarkGuest) data-guest="1" @endif
        aria-pressed="{{ $bookmarkActive ? 'true' : 'false' }}"
        aria-label="{{ $bookmarkActive ? 'Remove bookmark' : 'Bookmark' }}"
        title="{{ $bookmarkActive ? 'Remove from bookmarks' : 'Save to bookmarks' }}"
    >
        <i class="bi bi-bookmark{{ $bookmarkActive ? '-fill' : '' }}" aria-hidden="true"></i>
    </button>

    @if(! $bookmarkGuest && $bookmarkId)
        <button
            type="button"
            class="fh-bookmark-note"
            data-bookmark-note
            data-update="{{ $bookmarkUpdateUrl }}"
            data-note="{{ (string) ($note ?? '') }}"
            aria-label="Edit your note for this bookmark"
            title="Edit note"
        >
            <i class="bi {{ filled($note) ? 'bi-pencil-fill' : 'bi-pencil-square' }}" aria-hidden="true"></i>
        </button>
    @endif
</span>
