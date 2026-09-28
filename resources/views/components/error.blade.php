@props([
    'message' => 'We could not load this section. Please try again.',
    'title' => 'Something went wrong',
])

<div {{ $attributes->merge(['class' => 'fh-error']) }} role="alert">
    <span class="fh-state-icon" aria-hidden="true"><i class="bi bi-exclamation-octagon"></i></span>
    <h3 class="fh-state-title">{{ $title }}</h3>
    <p class="fh-state-message">{{ $message }}</p>

    @if($slot->isNotEmpty())
        <div class="fh-state-actions">{{ $slot }}</div>
    @endif
</div>
