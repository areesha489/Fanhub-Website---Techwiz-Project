@props([
    'value',
    'label' => null,
    'map' => null,
])

@php
    $defaultMap = [
        'draft' => ['variant' => 'slate', 'label' => 'Draft'],
        'published' => ['variant' => 'green', 'label' => 'Published'],
        'archived' => ['variant' => 'slate', 'label' => 'Archived'],
        'active' => ['variant' => 'green', 'label' => 'Active'],
        'suspended' => ['variant' => 'red', 'label' => 'Suspended'],
        'admin' => ['variant' => 'violet', 'label' => 'Admin'],
        'user' => ['variant' => 'cyan', 'label' => 'Member'],
        'pending' => ['variant' => 'amber', 'label' => 'Pending'],
        'approved' => ['variant' => 'green', 'label' => 'Approved'],
        'rejected' => ['variant' => 'red', 'label' => 'Rejected'],
        'open' => ['variant' => 'red', 'label' => 'Open'],
        'in_review' => ['variant' => 'amber', 'label' => 'In review'],
        'resolved' => ['variant' => 'green', 'label' => 'Resolved'],
        'closed' => ['variant' => 'slate', 'label' => 'Closed'],
        'upcoming' => ['variant' => 'cyan', 'label' => 'Upcoming'],
        'ongoing' => ['variant' => 'blue', 'label' => 'Ongoing'],
        'completed' => ['variant' => 'green', 'label' => 'Completed'],
        'cancelled' => ['variant' => 'red', 'label' => 'Cancelled'],
        'released' => ['variant' => 'green', 'label' => 'Released'],
        'delayed' => ['variant' => 'amber', 'label' => 'Delayed'],
        'active_flag' => ['variant' => 'green', 'label' => 'Active'],
        'inactive' => ['variant' => 'slate', 'label' => 'Inactive'],
    ];

    $badgeMap = is_array($map) && $map !== [] ? array_merge($defaultMap, $map) : $defaultMap;
    $badgeValue = is_bool($value) ? ($value ? 'active' : 'inactive') : (string) $value;
    $badgeEntry = $badgeMap[$badgeValue] ?? null;
    $badgeVariant = $badgeEntry['variant'] ?? 'slate';
    $badgeText = $label ?? ($badgeEntry['label'] ?? \Illuminate\Support\Str::headline($badgeValue));
@endphp

<x-badge :variant="$badgeVariant" {{ $attributes }}>
    {{ $badgeText }}
</x-badge>
