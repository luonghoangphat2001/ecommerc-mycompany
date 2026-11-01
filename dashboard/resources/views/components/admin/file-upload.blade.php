@props([
    'name',
    'id' => null,
    'accept' => 'image/*',
    'currentUrl' => null,
    'currentName' => null,
    'previewId' => null,
    'hint' => 'Hỗ trợ PNG, JPG, WEBP, SVG (Tối đa 5MB)',
])

@php
    $inputId = $id ?? $name;
    $resolvedPreviewId = $previewId ?? ('preview-' . $inputId);
@endphp

<div class="admin-upload-box" x-data="{ chosenFile: '' }">
    <div class="admin-upload-info">
        <div class="admin-upload-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" x2="12" y1="3" y2="15"/>
            </svg>
        </div>
        <div class="admin-upload-text">
            <strong x-text="chosenFile ? ('Đã chọn: ' + chosenFile) : 'Tải tệp hình ảnh lên hệ thống'">Tải tệp hình ảnh lên hệ thống</strong>
            <span>{{ $hint }}</span>
            @if ($currentName)
                <span class="mono" style="color: var(--fi-primary); font-size: 11px;">Tệp hiện tại: {{ $currentName }}</span>
            @endif
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 12px;">
        @if ($currentUrl)
            <img id="{{ $resolvedPreviewId }}" src="{{ $currentUrl }}" alt="" class="preview-img" style="max-height: 56px; width: 56px; object-fit: cover;">
        @else
            <img id="{{ $resolvedPreviewId }}" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="" class="preview-img" style="max-height: 56px; width: 56px; object-fit: cover; display: none;">
        @endif

        <label class="btn btn-secondary btn-sm admin-upload-trigger" for="{{ $inputId }}" style="margin: 0;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
            <span>Chọn tệp tải lên</span>
            <input
                type="file"
                id="{{ $inputId }}"
                name="{{ $name }}"
                accept="{{ $accept }}"
                @change="chosenFile = $event.target.files[0]?.name || ''; if (typeof previewImage === 'function') previewImage($event.target, '{{ $resolvedPreviewId }}')"
            >
        </label>
    </div>
</div>

