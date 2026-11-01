@props([
    'name',
    'id' => null,
    'options' => [],
    'selected' => [],
    'placeholder' => 'Chọn các mục...',
    'maxDisplayTags' => 2,
])

@php
    $elementId = $id ?: 'ms-' . \Illuminate\Support\Str::slug(str_replace(['[', ']'], ['-', ''], $name)) . '-' . \Illuminate\Support\Str::random(4);

    $optionsList = [];
    foreach ($options as $optValue => $optLabel) {
        $optionsList[] = [
            'value' => (string) $optValue,
            'label' => (string) $optLabel,
        ];
    }

    $rawSelected = is_array($selected)
        ? $selected
        : (is_string($selected) ? (json_decode($selected, true) ?: []) : []);

    $selectedValues = array_values(array_map('strval', array_filter($rawSelected, fn($v) => $v !== null && $v !== '')));

    $inputName = \Illuminate\Support\Str::endsWith($name, '[]') ? $name : ($name . '[]');
@endphp

<div
    id="{{ $elementId }}"
    x-data="{
        open: false,
        dropUp: false,
        search: '',
        options: {{ Js::from($optionsList) }},
        selected: {{ Js::from($selectedValues) }},
        maxTags: {{ (int) $maxDisplayTags }},

        get filteredOptions() {
            if (!this.search.trim()) return this.options;
            const q = this.search.toLowerCase().trim();
            return this.options.filter(opt => String(opt.label).toLowerCase().includes(q));
        },

        get selectedOptions() {
            const set = new Set(this.selected);
            return this.options.filter(opt => set.has(String(opt.value)));
        },

        isSelected(val) {
            return this.selected.includes(String(val));
        },

        toggle(val) {
            val = String(val);
            const idx = this.selected.indexOf(val);
            if (idx > -1) {
                this.selected.splice(idx, 1);
            } else {
                this.selected.push(val);
            }
        },

        selectAll() {
            const currentFiltered = this.filteredOptions.map(opt => String(opt.value));
            const newSet = new Set([...this.selected, ...currentFiltered]);
            this.selected = Array.from(newSet);
        },

        deselectAll() {
            if (!this.search.trim()) {
                this.selected = [];
            } else {
                const currentFiltered = new Set(this.filteredOptions.map(opt => String(opt.value)));
                this.selected = this.selected.filter(val => !currentFiltered.has(val));
            }
        },

        remove(val) {
            this.selected = this.selected.filter(v => v !== String(val));
        },

        clearAll() {
            this.selected = [];
        },

        checkDropDirection() {
            const rect = this.$el.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            this.dropUp = spaceBelow < 280 && rect.top > 280;
        }
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="ms-root multi-select-dropdown-wrapper"
    :class="{ 'is-open': open }"
>
    {{-- Fallback empty input so unselecting all still submits an empty array to backend --}}
    <input type="hidden" name="{{ $inputName }}" value="">

    {{-- Real submitted values --}}
    <template x-for="val in selected" :key="val">
        <input type="hidden" name="{{ $inputName }}" :value="val">
    </template>

    {{-- Trigger Control Bar (Strict Flex Row, No-Wrap Parent) --}}
    <div
        class="ms-trigger"
        :class="{ 'is-active': open }"
        role="combobox"
        aria-haspopup="listbox"
        :aria-expanded="open"
        tabindex="0"
        @keydown.space.prevent="open = !open; if(open) { checkDropDirection(); $nextTick(() => $refs.searchInput.focus()); }"
        @keydown.enter.prevent="open = !open; if(open) { checkDropDirection(); $nextTick(() => $refs.searchInput.focus()); }"
        @click="open = !open; if(open) { checkDropDirection(); $nextTick(() => $refs.searchInput.focus()); }"
    >
        {{-- Tags Area (Wraps chips nicely inside left container) --}}
        <div class="ms-tags-box">
            <template x-if="selected.length === 0">
                <span class="ms-placeholder">{{ $placeholder }}</span>
            </template>

            <template x-for="(opt, idx) in selectedOptions.slice(0, maxTags)" :key="opt.value">
                <span class="ms-tag">
                    <span class="ms-tag-label" x-text="opt.label"></span>
                    <button
                        type="button"
                        class="ms-tag-remove"
                        @click.stop="remove(opt.value)"
                        title="Bỏ chọn"
                        aria-label="Bỏ chọn"
                    >
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </span>
            </template>

            <template x-if="selected.length > maxTags">
                <span class="ms-tag-more" x-text="'+' + (selected.length - maxTags) + ' khác'"></span>
            </template>
        </div>

        {{-- Right Indicators (Strictly Flex-Shrink: 0, Centered) --}}
        <div class="ms-indicators">
            <template x-if="selected.length > 0">
                <button
                    type="button"
                    class="ms-clear-btn"
                    @click.stop="clearAll()"
                    title="Xóa tất cả"
                    aria-label="Xóa tất cả"
                >
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </template>
            <span class="ms-chevron" :class="{ 'is-flipped': open }">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </div>
    </div>

    {{-- Floating Dropdown Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 transform scale-98"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-98"
        class="ms-panel"
        :class="dropUp ? 'ms-panel-up' : 'ms-panel-down'"
        style="display: none;"
    >
        {{-- Search Input & Action Bar --}}
        <div class="ms-panel-header">
            <div class="ms-search-wrap">
                <svg class="ms-search-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    x-ref="searchInput"
                    x-model="search"
                    class="ms-search-input"
                    placeholder="Gõ để tìm nhanh..."
                    @click.stop
                >
                <button
                    type="button"
                    x-show="search.length > 0"
                    @click="search = ''; $refs.searchInput.focus()"
                    class="ms-search-clear"
                    title="Xóa tìm kiếm"
                >
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="ms-actions-bar">
                <span class="ms-count-badge" x-text="'Đã chọn: ' + selected.length + '/' + options.length"></span>
                <div class="ms-quick-links">
                    <button type="button" @click="selectAll()" class="ms-link ms-link-primary">Chọn tất cả</button>
                    <span class="ms-link-dot">•</span>
                    <button type="button" @click="deselectAll()" class="ms-link ms-link-danger">Bỏ chọn</button>
                </div>
            </div>
        </div>

        {{-- Options Scrollable List --}}
        <div class="ms-options-list">
            <template x-for="opt in filteredOptions" :key="opt.value">
                <label
                    class="ms-option-item"
                    :class="{ 'is-selected': isSelected(opt.value) }"
                >
                    <input
                        type="checkbox"
                        class="ms-option-checkbox"
                        :value="opt.value"
                        :checked="isSelected(opt.value)"
                        @change="toggle(opt.value)"
                    >
                    <span class="ms-option-text" x-text="opt.label"></span>
                </label>
            </template>

            <template x-if="filteredOptions.length === 0">
                <div class="ms-empty-state">
                    Không tìm thấy mục nào phù hợp
                </div>
            </template>
        </div>
    </div>
</div>

@once
<style>
    .ms-root {
        position: relative;
        width: 100%;
        font-family: inherit;
        box-sizing: border-box;
    }
    .ms-root * {
        box-sizing: border-box;
    }
    .ms-root.is-open {
        z-index: 100;
    }

    /* Trigger Bar */
    .ms-trigger {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        min-height: 38px;
        width: 100%;
        padding: 4px 10px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        cursor: pointer;
        gap: 8px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        user-select: none;
    }
    .ms-trigger:hover {
        border-color: #94a3b8;
    }
    .ms-trigger.is-active,
    .ms-trigger:focus-visible {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.14);
    }

    /* Tags container */
    .ms-tags-box {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        flex: 1 1 auto;
        min-width: 0;
    }
    .ms-placeholder {
        color: #94a3b8;
        font-size: 13px;
        line-height: 1.4;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ms-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f1f5f9;
        color: #0f172a;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 11.5px;
        font-weight: 500;
        max-width: 160px;
        white-space: nowrap;
    }
    .ms-tag-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1.3;
    }
    .ms-tag-remove {
        border: none;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        padding: 0;
        margin: 0;
        border-radius: 2px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        transition: color 0.1s ease;
    }
    .ms-tag-remove:hover {
        color: #dc2626;
    }
    .ms-tag-more {
        display: inline-flex;
        align-items: center;
        background: #e2e8f0;
        color: #475569;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Right Indicators */
    .ms-indicators {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 6px !important;
        flex: 0 0 auto !important;
        margin-left: auto;
    }
    .ms-clear-btn {
        border: none;
        background: #f1f5f9;
        color: #64748b;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        margin: 0;
        line-height: 1;
        transition: all 0.12s ease;
    }
    .ms-clear-btn:hover {
        background: #fee2e2;
        color: #dc2626;
    }
    .ms-chevron {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        transition: transform 0.18s ease;
    }
    .ms-chevron.is-flipped {
        transform: rotate(180deg);
    }

    /* Floating Panel */
    .ms-panel {
        position: absolute;
        left: 0;
        right: 0;
        width: 100%;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.16), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
        z-index: 1000;
        overflow: hidden;
    }
    .ms-panel-down {
        top: calc(100% + 4px);
        bottom: auto;
    }
    .ms-panel-up {
        bottom: calc(100% + 4px);
        top: auto;
    }

    /* Panel Header */
    .ms-panel-header {
        padding: 8px 10px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .ms-search-wrap {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }
    .ms-search-icon {
        position: absolute;
        left: 9px;
        color: #94a3b8;
        pointer-events: none;
    }
    .ms-search-input {
        width: 100% !important;
        height: 32px !important;
        min-height: 32px !important;
        padding: 0 24px 0 28px !important;
        font-size: 12.5px !important;
        color: #0f172a !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        outline: none !important;
        background: #ffffff !important;
        box-shadow: none !important;
    }
    .ms-search-input:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.12) !important;
    }
    .ms-search-clear {
        position: absolute;
        right: 6px;
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        padding: 2px;
        display: inline-flex;
        align-items: center;
    }
    .ms-search-clear:hover {
        color: #334155;
    }

    /* Actions Sub-bar */
    .ms-actions-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11.5px;
        color: #64748b;
        padding: 0 2px;
    }
    .ms-count-badge {
        font-weight: 500;
    }
    .ms-quick-links {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .ms-link {
        border: none;
        background: transparent;
        font-weight: 600;
        cursor: pointer;
        padding: 0;
        font-size: 11.5px;
        line-height: 1;
    }
    .ms-link-primary {
        color: #4f46e5;
    }
    .ms-link-primary:hover {
        text-decoration: underline;
    }
    .ms-link-danger {
        color: #dc2626;
    }
    .ms-link-danger:hover {
        text-decoration: underline;
    }
    .ms-link-dot {
        color: #cbd5e1;
        font-size: 9px;
    }

    /* Options List */
    .ms-options-list {
        max-height: 200px;
        overflow-y: auto;
        padding: 4px 0;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .ms-option-item {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 6px 12px !important;
        cursor: pointer !important;
        user-select: none !important;
        transition: background-color 0.1s ease !important;
        font-size: 12.5px !important;
        color: #334155 !important;
        margin: 0 !important;
        border: none !important;
        font-weight: normal !important;
    }
    .ms-option-item:hover {
        background-color: #f8fafc !important;
    }
    .ms-option-item.is-selected {
        background-color: #f1f5f9 !important;
        font-weight: 600 !important;
        color: #0f172a !important;
    }
    .ms-option-checkbox {
        accent-color: #4f46e5;
        width: 15px !important;
        height: 15px !important;
        min-height: 15px !important;
        border-radius: 3px !important;
        cursor: pointer !important;
        flex-shrink: 0 !important;
        margin: 0 !important;
    }
    .ms-option-text {
        flex: 1 1 auto;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1.35;
    }
    .ms-empty-state {
        padding: 18px 12px;
        text-align: center;
        color: #94a3b8;
        font-size: 12px;
    }
</style>
@endonce
