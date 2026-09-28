@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'id' => null,
    'hint' => null,
    'help' => null,
    'error' => null,
    'required' => false,
    'multiple' => false,
])

@php
    $selectId = $id ?? $name;
    $selectName = $multiple ? $name.'[]' : $name;
    $selectValue = old($name, $selected);
    $selectValues = array_map('strval', array_values((array) ($selectValue ?? [])));
    $placeholder = $placeholder;
@endphp

<div {{ $attributes->merge(['class' => 'mb-3']) }}>
    <label class="fh-label" for="{{ $selectId }}">
        {{ $label }}
        @if($required)
            <span class="req" aria-hidden="true">*</span>
        @endif
        @if($hint)
            <span class="fh-label-hint">{{ $hint }}</span>
        @endif
    </label>

    <select
        class="fh-field"
        id="{{ $selectId }}"
        name="{{ $selectName }}"
        @if($multiple) multiple size="5" @endif
        @if($required) required @endif
    >
        @if($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selectValues, true))>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @if($error)
        <div class="fh-invalid-feedback d-block" role="alert">{{ $error }}</div>
    @elseif($help)
        <div class="fh-help">{{ $help }}</div>
    @endif
</div>
