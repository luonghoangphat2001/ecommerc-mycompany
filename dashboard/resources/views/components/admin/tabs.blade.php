@props([
    'tabs' => [],
    'active' => 0,
    'mode' => 'alpine', // 'alpine' | 'link'
])

@if (!empty($tabs))
    <div class="admin-tabs-bar" role="tablist">
        @foreach ($tabs as $index => $tab)
            @php
                $isArray = is_array($tab);
                $label = $isArray ? ($tab['label'] ?? $index) : $tab;
                $url = $isArray ? ($tab['url'] ?? '#') : '#';
                $badge = $isArray ? ($tab['badge'] ?? null) : null;
                $key = $isArray ? ($tab['key'] ?? $index) : $index;
            @endphp

            @if ($mode === 'link')
                <a
                    href="{{ $url }}"
                    role="tab"
                    class="admin-tab-btn {{ (string) $active === (string) $key ? 'active' : '' }}"
                >
                    <span>{{ $label }}</span>
                    @if ($badge !== null)
                        <span class="admin-tab-badge">{{ $badge }}</span>
                    @endif
                </a>
            @else
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = {{ is_numeric($key) ? $key : "'" . $key . "'" }}"
                    class="workspace-tab-btn"
                    :class="{ 'active': activeTab === {{ is_numeric($key) ? $key : "'" . $key . "'" }} }"
                >
                    <span class="admin-tab-badge">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <span>{{ $label }}</span>
                </button>
            @endif
        @endforeach
    </div>
@endif

