@props([
    'status' => 'secondary',
    'label' => null,
])

@php
    $normalized = strtolower((string) ($status instanceof \BackedEnum ? $status->value : $status));
    $map = [
        'pending' => 'badge-pending',
        'warning' => 'badge-warning',
        'partial' => 'badge-warning',
        'suspended' => 'badge-warning',
        'in_progress' => 'badge-warning',
        'new' => 'badge-info',
        'processing' => 'badge-info',
        'shipping' => 'badge-info',
        'delivering' => 'badge-info',
        'info' => 'badge-info',
        'completed' => 'badge-success',
        'paid' => 'badge-success',
        'approved' => 'badge-success',
        'active' => 'badge-success',
        'positive' => 'badge-success',
        'success' => 'badge-success',
        'cancelled' => 'badge-danger',
        'failed' => 'badge-danger',
        'rejected' => 'badge-danger',
        'defective' => 'badge-danger',
        'expired' => 'badge-danger',
        'negative' => 'badge-danger',
        'open' => 'badge-danger',
        'full' => 'badge-danger',
        'danger' => 'badge-danger',
    ];
    $badgeClass = $map[$normalized] ?? 'badge-secondary';
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $badgeClass]) }}>
    {{ $label ?? $slot }}
</span>

