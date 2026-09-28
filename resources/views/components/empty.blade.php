@props([
    'title' => 'Nothing here yet',
    'message' => 'Once there is something to show, it will appear right here.',
    'icon' => 'bi-inbox',
])

<div {{ $attributes->merge(['class' => 'fh-empty']) }} role="status">
    <span class="fh-state-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
    <h3 class="fh-state-title">{{ $title }}</h3>
    <p class="fh-state-message">{{ $message }}</p>

    @if($slot->isNotEmpty())
        <div class="fh-state-actions">{{ $slot }}</div>
    @endif
</div>
