@props([
    'user' => null,
    'size' => 'md',
])

@php
    $avatarSizes = ['sm', 'md', 'lg', 'xl'];
    $avatarSize = in_array($size, $avatarSizes, true) ? $size : 'md';

    $avatarName = (string) data_get($user, 'name', 'Guest');
    $avatarPhoto = data_get($user, 'avatar');

    $avatarInitials = collect(preg_split('/\s+/', trim($avatarName)))
        ->filter()
        ->take(2)
        ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->join('');

    if ($avatarInitials === '') {
        $avatarInitials = 'FU';
    }

    $avatarSizeClass = $avatarSize === 'md' ? '' : ' fh-avatar-'.$avatarSize;
@endphp

@if($avatarPhoto)
    <img
        {{ $attributes->merge(['class' => 'fh-avatar'.$avatarSizeClass]) }}
        src="{{ asset('storage/'.ltrim($avatarPhoto, '/')) }}"
        alt="{{ $avatarName }} avatar"
        width="{{ $avatarSize === 'xl' ? 88 : ($avatarSize === 'lg' ? 54 : ($avatarSize === 'sm' ? 30 : 38)) }}"
        height="{{ $avatarSize === 'xl' ? 88 : ($avatarSize === 'lg' ? 54 : ($avatarSize === 'sm' ? 30 : 38)) }}"
        loading="lazy"
        decoding="async"
    >
@else
    <span
        {{ $attributes->merge(['class' => 'fh-avatar'.$avatarSizeClass]) }}
        role="img"
        aria-label="{{ $avatarName }}"
        title="{{ $avatarName }}"
    >{{ $avatarInitials }}</span>
@endif
