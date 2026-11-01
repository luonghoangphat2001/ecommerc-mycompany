@props([
    'name' => 'q',
    'value' => null,
    'placeholder' => null,
    'label' => null,
    'width' => '250px',
])

@php
    $resolvedValue = $value ?? request($name);
    $resolvedPlaceholder = $placeholder ?? (__('admin.actions.search') . '...');
@endphp

<div class="admin-search-wrap" style="width: {{ $width }}; flex: 0 0 {{ $width }};">
    @if ($label)
        <label for="search-{{ $name }}">{{ $label }}</label>
    @endif
    <div style="position: relative;">
        <svg class="admin-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/>
        </svg>
        <input
            id="search-{{ $name }}"
            type="text"
            name="{{ $name }}"
            value="{{ $resolvedValue }}"
            placeholder="{{ $resolvedPlaceholder }}"
            {{ $attributes->merge(['style' => 'padding-left: 36px !important;']) }}
        >
    </div>
</div>

