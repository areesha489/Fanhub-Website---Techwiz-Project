@props([
    'label',
    'for',
    'hint' => null,
    'help' => null,
    'error' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => 'mb-3']) }}>
    <label class="fh-label" for="{{ $for }}">
        {{ $label }}
        @if($required)
            <span class="req" aria-hidden="true">*</span>
        @endif
        @if($hint)
            <span class="fh-label-hint">{{ $hint }}</span>
        @endif
    </label>

    {{ $slot }}

    @if($error)
        <div class="fh-invalid-feedback d-block" role="alert">{{ $error }}</div>
    @elseif($help)
        <div class="fh-help">{{ $help }}</div>
    @endif
</div>
