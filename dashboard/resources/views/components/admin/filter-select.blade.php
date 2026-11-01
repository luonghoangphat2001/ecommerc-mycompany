@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => '-- Tất cả --',
    'showPlaceholder' => true,
    'width' => '185px',
])

@php
    $resolvedSelected = $selected !== null ? $selected : request($name);
@endphp

<div class="admin-filter-item" style="min-width: {{ $width }};">
    @if ($label)
        <label for="filter-{{ $name }}">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: #64748b;"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            <span>{{ $label }}</span>
        </label>
    @endif
    <select id="filter-{{ $name }}" name="{{ $name }}" {{ $attributes }}>
        @if ($showPlaceholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected((string) $resolvedSelected === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
        {{ $slot }}
    </select>
</div>

