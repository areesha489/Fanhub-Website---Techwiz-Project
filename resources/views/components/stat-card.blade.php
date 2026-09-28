@props([
    'label',
    'value',
    'icon' => 'bi-graph-up',
    'tone' => 'violet',
    'delta' => null,
])

@php
    $statTones = [
        'violet' => '#8B5CF6',
        'pink' => '#EC4899',
        'magenta' => '#EC4899',
        'cyan' => '#22D3EE',
        'blue' => '#3B82F6',
        'green' => '#34D399',
        'amber' => '#FBBF24',
        'coral' => '#FB7185',
        'red' => '#F87171',
        'slate' => '#94A3C4',
    ];

    $statColor = $statTones[$tone] ?? $statTones['violet'];
    $statDelta = is_array($delta)
        ? ($delta['value'] ?? null)
        : $delta;
    $statDirection = is_array($delta)
        ? ($delta['direction'] ?? null)
        : null;

    if ($statDirection === null && is_string($statDelta)) {
        $statDirection = str_starts_with($statDelta, '-') ? 'down' : 'up';
    }

    $statDirection = in_array($statDirection, ['up', 'down', 'flat'], true) ? $statDirection : 'up';
@endphp

<div
    {{ $attributes->merge(['class' => 'fh-stat']) }}
    style="--fh-stat-color: {{ $statColor }};"
>
    <span class="fh-stat-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>

    <div class="fh-stat-body">
        <div class="fh-stat-value">{{ $value }}</div>
        <div class="fh-stat-label">{{ $label }}</div>
    </div>

    @if($statDelta !== null && $statDelta !== '')
        <span class="fh-stat-delta {{ $statDirection }}">
            <i class="bi {{ $statDirection === 'up' ? 'bi-arrow-up-right' : ($statDirection === 'down' ? 'bi-arrow-down-right' : 'bi-dash') }}" aria-hidden="true"></i>
            {{ $statDelta }}
        </span>
    @endif
</div>
