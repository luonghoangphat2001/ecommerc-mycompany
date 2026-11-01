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
        <div class="table-panel card settings-panel">
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
                                $wide = in_array($type, ['textarea', 'json', 'image'], true);
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
