@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <x-admin.page-header
        :title="__('admin.sidebar.settings')"
        :description="__('admin.settings.description')"
    />

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    @php
        $tabIcons = [
            'general' => '<line x1="4" x2="4" y1="21" y2="14"/><line x1="4" x2="4" y1="10" y2="3"/><line x1="12" x2="12" y1="21" y2="12"/><line x1="12" x2="12" y1="8" y2="3"/><line x1="20" x2="20" y1="21" y2="16"/><line x1="20" x2="20" y1="12" y2="3"/><line x1="2" x2="6" y1="14" y2="14"/><line x1="10" x2="14" y1="8" y2="8"/><line x1="18" x2="22" y1="16" y2="16"/>',
            'settings' => '<circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>',
            'ecommerce' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
            'products' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
            'checkout' => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
            'shipping' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18.5" r="2.5"/><circle cx="7" cy="18.5" r="2.5"/>',
            'inventory' => '<path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"/><path d="M6 18h12"/><path d="M6 14h12"/>',
            'loyalty' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'marketing' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
            'footer' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>',
            'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
            'webhook' => '<path d="M18 16.98h-5.99c-1.1 0-1.95.94-2.48 1.9A4 4 0 0 1 2 17c0-2.21 1.79-4 4-4h5.5"/><circle cx="12" cy="5" r="3"/><circle cx="20" cy="17" r="2"/><circle cx="4" cy="17" r="2"/>',
            'api' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
            'advanced' => '<polyline points="4 17 10 11 4 5"/><line x1="12" x2="20" y1="19" y2="19"/>',
            'custom' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
            'departments' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        ];
        $section = $schema[$activeTab];
    @endphp

    <div class="settings-shell">
        {{-- Giao diện trái: Cột danh mục cài đặt (Shopify Sidebar Navigation) --}}
        <aside class="settings-tabs" aria-label="{{ __('admin.sidebar.settings') }}">
            <div class="settings-nav-header">
                <span class="settings-nav-title">{{ __('admin.sidebar.settings') }}</span>
            </div>
            @foreach ($schema as $group => $item)
                @php
                    $isActive = ($activeTab === $group);
                @endphp
                <a href="{{ route('admin.settings.index', ['tab' => $group]) }}" class="settings-tab {{ $isActive ? 'active' : '' }}" title="{{ __($item['label']) }}">
                    <span class="settings-tab-label">{{ __($item['label']) }}</span>
                </a>
            @endforeach
        </aside>

        {{-- Giao diện phải: Nội dung chi tiết cấu hình --}}
        <div class="table-panel card settings-panel" style="overflow: visible;">
            <div class="panel-heading settings-heading">
                <div>
                    <h2>{{ __($section['label']) }}</h2>
                    <p>{{ __($section['description'] ?? 'admin.settings.update') }}</p>
                </div>
            </div>

            <form method="post" action="{{ route('admin.settings.update-group') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="group" value="{{ $activeTab }}">

                @if (($section['fields'] ?? []) !== [])
                    <div class="settings-field-grid">
                        @foreach ($section['fields'] as $name => $field)
                            @php
                                $type = $field['type'] ?? 'text';
                                $rawValue = data_get($values, $activeTab . '.' . $name, $field['default'] ?? null);
                                $value = is_array($rawValue) ? json_encode($rawValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $rawValue;
                                $wide = in_array($type, ['textarea', 'json', 'image', 'payment_gateways', 'exchange_rates', 'checkboxes', 'multiselect', 'multi_select'], true);
                                $currentImage = is_string($rawValue) && $rawValue !== '' ? \Illuminate\Support\Facades\Storage::disk('public')->url($rawValue) : null;
                            @endphp

                            <div class="settings-field {{ $wide ? 'settings-field-wide' : '' }}">
                                <label for="setting-{{ $name }}">{{ __($field['label'] ?? $name) }}</label>

                                @if ($type === 'boolean')
                                    <input type="hidden" name="settings[{{ $name }}]" value="0">
                                    <label class="toggle-row" for="setting-{{ $name }}">
                                        <input id="setting-{{ $name }}" type="checkbox" name="settings[{{ $name }}]" value="1" @checked((bool) old('settings.' . $name, $rawValue))>
                                        <span class="toggle-switch"></span>
                                        <span>{{ (bool) $rawValue ? __('admin.settings.enabled') : __('admin.settings.disabled') }}</span>
                                    </label>
                                @elseif ($type === 'checkboxes')
                                    @php
                                        $selectedValues = is_array($rawValue) ? $rawValue : (is_string($rawValue) ? (json_decode($rawValue, true) ?: []) : []);
                                        $selectedValues = array_map('strval', $selectedValues);
                                    @endphp
                                    <input type="hidden" name="settings[{{ $name }}][]" value="">
                                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: 8px; padding: 10px; background: #f8fafc; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 8px;">
                                        @forelse (($field['options'] ?? []) as $optKey => $optLabel)
                                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; user-select: none; background: #ffffff; padding: 6px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                                <input type="checkbox" name="settings[{{ $name }}][]" value="{{ $optKey }}" @checked(in_array((string)$optKey, $selectedValues, true)) style="accent-color: var(--fi-primary, #4f46e5); width: 16px; height: 16px;">
                                                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500;">{{ $optLabel }}</span>
                                            </label>
                                        @empty
                                            <div style="color: #64748b; font-size: 12px; grid-column: 1 / -1;">Chưa có dữ liệu lựa chọn</div>
                                        @endforelse
                                    </div>
                                @elseif ($type === 'multiselect' || $type === 'multi_select')
                                    <x-admin.multi-select
                                        :name="'settings[' . $name . ']'"
                                        :id="'setting-' . $name"
                                        :options="$field['options'] ?? []"
                                        :selected="old('settings.' . $name, $rawValue)"
                                        :placeholder="'Chọn ' . mb_strtolower(__($field['label'] ?? $name), 'UTF-8') . '...'"
                                    />
                                @elseif ($type === 'select')
                                    <select id="setting-{{ $name }}" name="settings[{{ $name }}]">
                                        @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" @selected((string) old('settings.' . $name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($type === 'image')
                                    <x-admin.file-upload
                                        :name="'settings[' . $name . ']'"
                                        :id="'setting-' . $name"
                                        accept="image/*"
                                        :currentUrl="$currentImage"
                                        :currentName="is_string($rawValue) && $rawValue !== '' ? $rawValue : null"
                                        :previewId="'preview-setting-' . $name"
                                    />
                                @elseif ($type === 'payment_gateways')
                                    @php
                                        $selectedGateways = is_array($rawValue) ? $rawValue : (is_string($rawValue) ? (json_decode($rawValue, true) ?: []) : ['cod', 'bank_transfer']);
                                        $availableGateways = [
                                            'cod' => [
                                                'name' => 'Thanh toán khi nhận hàng (COD)',
                                                'desc' => 'Thu tiền mặt trực tiếp khi giao hàng thành công tận tay khách.',
                                                'badge' => 'Tiền mặt',
                                                'badge_color' => '#15803d',
                                                'badge_bg' => '#dcfce7',
                                            ],
                                            'bank_transfer' => [
                                                'name' => 'Chuyển khoản ngân hàng (Bank Transfer)',
                                                'desc' => 'Hiển thị số tài khoản ngân hàng và mã đơn hàng để khách chuyển khoản.',
                                                'badge' => 'Ngân hàng',
                                                'badge_color' => '#0369a1',
                                                'badge_bg' => '#e0f2fe',
                                            ],
                                            'vnpay' => [
                                                'name' => 'Cổng thanh toán VNPAY',
                                                'desc' => 'Thanh toán quét mã VNPAY-QR, thẻ ATM nội địa & Mobile Banking.',
                                                'badge' => 'VNPAY-QR',
                                                'badge_color' => '#b91c1c',
                                                'badge_bg' => '#fee2e2',
                                            ],
                                            'momo' => [
                                                'name' => 'Ví điện tử MoMo',
                                                'desc' => 'Thanh toán tức thời qua ứng dụng Ví điện tử MoMo trên di động.',
                                                'badge' => 'Ví MoMo',
                                                'badge_color' => '#a21caf',
                                                'badge_bg' => '#fdf4ff',
                                            ],
                                            'stripe' => [
                                                'name' => 'Thẻ quốc tế Stripe (Visa / Mastercard)',
                                                'desc' => 'Chấp nhận thanh toán thẻ quốc tế Visa, Mastercard, JCB bảo mật chuẩn PCI-DSS.',
                                                'badge' => 'Thẻ quốc tế',
                                                'badge_color' => '#4338ca',
                                                'badge_bg' => '#e0e7ff',
                                            ],
                                            'paypal' => [
                                                'name' => 'Cổng thanh toán PayPal',
                                                'desc' => 'Thanh toán an toàn qua tài khoản PayPal cho khách quốc tế.',
                                                'badge' => 'PayPal',
                                                'badge_color' => '#0f766e',
                                                'badge_bg' => '#ccfbf1',
                                            ],
                                        ];
                                    @endphp

                                    <input type="hidden" name="settings[{{ $name }}][]" value="">
                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 12px; margin-top: 8px;">
                                        @foreach ($availableGateways as $gwKey => $gw)
                                            @php
                                                $isChecked = in_array($gwKey, $selectedGateways, true);
                                            @endphp
                                            <label style="display: flex; align-items: flex-start; gap: 12px; padding: 12px 14px; border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: var(--fi-radius-md, 8px); background: #ffffff; cursor: pointer; user-select: none;">
                                                <input type="checkbox" name="settings[{{ $name }}][]" value="{{ $gwKey }}" @checked($isChecked) style="accent-color: var(--fi-primary, #4f46e5); width: 16px; height: 16px; margin-top: 3px;">
                                                <div style="flex: 1; min-width: 0;">
                                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 3px;">
                                                        <strong style="font-size: 13px; color: var(--fi-text, #0f172a);">{{ $gw['name'] }}</strong>
                                                        <span style="font-size: 11px; font-weight: 600; color: {{ $gw['badge_color'] }}; background: {{ $gw['badge_bg'] }}; padding: 1px 7px; border-radius: 4px; white-space: nowrap;">{{ $gw['badge'] }}</span>
                                                    </div>
                                                    <div style="font-size: 12px; color: var(--fi-muted, #64748b); line-height: 1.4;">{{ $gw['desc'] }}</div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($type === 'exchange_rates')
                                    @php
                                        $rates = is_array($rawValue) ? $rawValue : (is_string($rawValue) ? (json_decode($rawValue, true) ?: []) : ['VND' => 1, 'USD' => 25000]);
                                        if (!isset($rates['VND'])) $rates['VND'] = 1;
                                    @endphp
                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; margin-top: 8px;">
                                        <div style="padding: 10px 12px; background: #f8fafc; border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: var(--fi-radius-md, 8px);">
                                            <div style="font-weight: 600; font-size: 12px; color: var(--fi-muted, #64748b); margin-bottom: 4px;">Tiền tệ gốc: VND</div>
                                            <input type="text" value="1 VND" class="form-control" disabled style="background: #e2e8f0; font-weight: 600;">
                                            <input type="hidden" name="settings[{{ $name }}][VND]" value="1">
                                        </div>
                                        <div style="padding: 10px 12px; background: #ffffff; border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: var(--fi-radius-md, 8px);">
                                            <div style="font-weight: 600; font-size: 12px; color: var(--fi-text, #0f172a); margin-bottom: 4px;">Đô la Mỹ (USD) = ? VND</div>
                                            <input type="number" name="settings[{{ $name }}][USD]" value="{{ $rates['USD'] ?? 25000 }}" class="form-control" placeholder="25000">
                                        </div>
                                        <div style="padding: 10px 12px; background: #ffffff; border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: var(--fi-radius-md, 8px);">
                                            <div style="font-weight: 600; font-size: 12px; color: var(--fi-text, #0f172a); margin-bottom: 4px;">Euro (EUR) = ? VND</div>
                                            <input type="number" name="settings[{{ $name }}][EUR]" value="{{ $rates['EUR'] ?? 27000 }}" class="form-control" placeholder="27000">
                                        </div>
                                    </div>
                                @elseif ($type === 'footer_menu')
                                    @php
                                        $allMenus = \App\Models\Menu::with(['items' => function($q) {
                                            $orderCol = \Illuminate\Support\Facades\Schema::hasColumn('menu_items', 'order') ? 'order' : 'id';
                                            $q->orderBy($orderCol);
                                        }])->orderBy('name')->get();
                                        $selectedHandle = old('settings.' . $name, $value) ?: 'footer-menu';
                                        $selectedMenu = $allMenus->firstWhere('handle', $selectedHandle) ?? $allMenus->first();
                                    @endphp
                                    <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 6px;">
                                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                            <div style="flex: 1; min-width: 260px;">
                                                <select id="setting-{{ $name }}" name="settings[{{ $name }}]" style="width: 100%; padding: 9px 12px; font-weight: 600; border: 1px solid var(--fi-border-subtle, #cbd5e1); border-radius: var(--fi-radius-md, 8px); background: #ffffff;">
                                                    @foreach ($allMenus as $menu)
                                                        <option value="{{ $menu->handle }}" @selected($selectedHandle === $menu->handle)>
                                                            {{ $menu->name }} ({{ $menu->handle }}) — {{ $menu->items->count() }} liên kết
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <a id="footer-menu-manage-btn" href="{{ route('admin.menus.index', ['menu_id' => $selectedMenu?->id]) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; border-radius: var(--fi-radius-md, 8px); font-size: 13px; font-weight: 600; text-decoration: none;">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                Quản lý mục liên kết Menu
                                            </a>
                                        </div>

                                        <div id="footer-menu-preview-box" style="background: #f8fafc; border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: var(--fi-radius-md, 8px); padding: 14px 16px;">
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0;">
                                                <div style="font-size: 13px; font-weight: 700; color: var(--fi-text, #0f172a); display: flex; align-items: center; gap: 8px;">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                    <span>Menu đang liên kết: <strong id="preview-menu-name">{{ $selectedMenu?->name ?? 'N/A' }}</strong></span>
                                                </div>
                                                <span style="font-size: 11px; font-weight: 600; background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                                    Đồng bộ tự động từ Menu
                                                </span>
                                            </div>
                                            <div id="footer-menu-items-table-container"></div>
                                        </div>
                                    </div>

                                    <script>
                                        (function() {
                                            const menusData = @json($allMenus);
                                            const selectEl = document.getElementById('setting-{{ $name }}');
                                            const manageBtn = document.getElementById('footer-menu-manage-btn');
                                            const previewName = document.getElementById('preview-menu-name');
                                            const tableContainer = document.getElementById('footer-menu-items-table-container');
                                            if (!selectEl || !tableContainer) return;

                                            function renderItems(menu) {
                                                if (!menu || !menu.items || menu.items.length === 0) {
                                                    tableContainer.innerHTML = '<div style="color: #64748b; font-size: 13px; padding: 8px 0; font-style: italic;">Menu này chưa có liên kết nào. Nhấn <strong>Quản lý mục liên kết Menu</strong> để thêm liên kết mới.</div>';
                                                    return;
                                                }
                                                let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px;">';
                                                menu.items.forEach(function(item, idx) {
                                                    let title = typeof item.title === 'object' && item.title !== null 
                                                        ? (item.title.vi || Object.values(item.title)[0]) 
                                                        : (item.title || 'Liên kết #' + (idx + 1));
                                                    html += '<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px;">' +
                                                        '<div style="min-width: 0; flex: 1;">' +
                                                            '<div style="font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' + (idx + 1) + '. ' + title + '</div>' +
                                                            '<div style="font-size: 11px; color: #64748b; font-family: monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">' + (item.url || '#') + '</div>' +
                                                        '</div>' +
                                                        '<span style="font-size: 10px; font-weight: 600; color: #475569; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; flex-shrink: 0;">' + (item.target || '_self') + '</span>' +
                                                    '</div>';
                                                });
                                                html += '</div>';
                                                tableContainer.innerHTML = html;
                                            }

                                            selectEl.addEventListener('change', function() {
                                                const val = this.value;
                                                const found = menusData.find(m => m.handle === val);
                                                if (found) {
                                                    if (manageBtn) manageBtn.href = "{{ route('admin.menus.index') }}?menu_id=" + found.id;
                                                    if (previewName) previewName.textContent = found.name;
                                                    renderItems(found);
                                                }
                                            });

                                            const initialMenu = menusData.find(m => m.handle === selectEl.value) || menusData[0];
                                            if (initialMenu) renderItems(initialMenu);
                                        })();
                                    </script>
                                @elseif (in_array($type, ['textarea', 'json'], true))
                                    <textarea id="setting-{{ $name }}" name="settings[{{ $name }}]" rows="{{ $type === 'json' ? 8 : 5 }}">{{ old('settings.' . $name, $value) }}</textarea>
                                    @if ($type === 'json')
                                        <small class="field-hint">{{ __('admin.settings.json_hint') }}</small>
                                    @endif
                                @elseif ($type === 'password')
                                    <input id="setting-{{ $name }}" type="password" name="settings[{{ $name }}]" value="" placeholder="{{ __('admin.settings.password_placeholder') }}">
                                @else
                                    <input id="setting-{{ $name }}" type="{{ $type }}" name="settings[{{ $name }}]" value="{{ old('settings.' . $name, $value) }}">
                                @endif

                                @if (!empty($field['hint']))
                                    <small class="field-hint">{{ $field['hint'] }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @php
                    $sectionActions = $section['management_actions'] ?? [];
                @endphp
                @if ($sectionActions !== [])
                    <div class="settings-actions-panel">
                        <div class="panel-heading settings-subheading">
                            <div>
                                <h3>{{ __('admin.settings.management') }}</h3>
                                <p>{{ __('admin.settings.management_desc') }}</p>
                            </div>
                        </div>
                        <div class="settings-action-grid">
                            @foreach ($sectionActions as $action)
                                @php
                                    $visibleKey = $action['visible_when'] ?? null;
                                    $visible = $visibleKey ? (bool) data_get($values, $activeTab . '.' . $visibleKey, $section['fields'][$visibleKey]['default'] ?? false) : true;
                                @endphp
                                @if ($visible)
                                    <a class="settings-action-card" href="{{ route($action['route']) }}">
                                        <strong>{{ __($action['label']) }}</strong>
                                        <span>{{ __('admin.settings.open_module') }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (($section['fields'] ?? []) !== [])
                    <div class="form-footer" style="padding: 14px 20px; margin-top: 0; background: #f8fafc;">
                        <button class="btn btn-primary" type="submit">{{ __('admin.settings.save_group', ['group' => __($section['label'])]) }}</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
