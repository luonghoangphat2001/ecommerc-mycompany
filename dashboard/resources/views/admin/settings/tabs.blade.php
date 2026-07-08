@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-description">Cấu hình hệ thống theo tab group, không còn dạng bảng thô.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="settings-shell">
        <aside class="settings-tabs card">
            @foreach ($schema as $group => $section)
                <a class="settings-tab {{ $activeTab === $group ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => $group]) }}">
                    <span>{{ $section['label'] }}</span>
                    <small>{{ count($section['fields']) }} fields</small>
                </a>
            @endforeach
        </aside>

        <section class="card settings-panel">
            @php($section = $schema[$activeTab])
            <div class="panel-heading settings-heading">
                <div>
                    <h2>{{ $section['label'] }}</h2>
                    <p>{{ $section['description'] ?? 'Cập nhật cấu hình.' }}</p>
                </div>
                <span class="settings-chip">{{ $activeTab }}</span>
            </div>

            <form method="post" action="{{ route('admin.settings.update-group') }}">
                @csrf
                <input type="hidden" name="group" value="{{ $activeTab }}">

                @if (($section['fields'] ?? []) !== [])
                    <div class="settings-field-grid">
                        @foreach ($section['fields'] as $name => $field)
                        @php($type = $field['type'] ?? 'text')
                        @php($rawValue = data_get($values, $activeTab . '.' . $name, $field['default'] ?? null))
                        @php($value = is_array($rawValue) ? json_encode($rawValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $rawValue)
                        @php($wide = in_array($type, ['textarea', 'json'], true))

                        <div class="settings-field {{ $wide ? 'settings-field-wide' : '' }}">
                            <label for="setting-{{ $name }}">{{ $field['label'] ?? $name }}</label>

                            @if ($type === 'boolean')
                                <input type="hidden" name="settings[{{ $name }}]" value="0">
                                <label class="toggle-row" for="setting-{{ $name }}">
                                    <input id="setting-{{ $name }}" type="checkbox" name="settings[{{ $name }}]" value="1" @checked((bool) old('settings.' . $name, $rawValue))>
                                    <span class="toggle-switch"></span>
                                    <span>{{ (bool) $rawValue ? 'Enabled' : 'Disabled' }}</span>
                                </label>
                            @elseif ($type === 'select')
                                <select id="setting-{{ $name }}" name="settings[{{ $name }}]">
                                    @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}" @selected((string) old('settings.' . $name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                            @elseif (in_array($type, ['textarea', 'json'], true))
                                <textarea id="setting-{{ $name }}" name="settings[{{ $name }}]" rows="{{ $type === 'json' ? 8 : 5 }}">{{ old('settings.' . $name, $value) }}</textarea>
                                @if ($type === 'json')
                                    <small class="field-hint">Nhập JSON hợp lệ nếu muốn lưu dạng array/object.</small>
                                @endif
                            @elseif ($type === 'password')
                                <input id="setting-{{ $name }}" type="password" name="settings[{{ $name }}]" value="" placeholder="Để trống để giữ nguyên">
                            @else
                                <input id="setting-{{ $name }}" type="{{ $type }}" name="settings[{{ $name }}]" value="{{ old('settings.' . $name, $value) }}">
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif

                @php($sectionActions = $section['management_actions'] ?? [])
                @if ($sectionActions !== [])
                    <div class="settings-actions-panel">
                        <div class="panel-heading settings-subheading">
                            <div>
                                <h3>Management</h3>
                                <p>Đi theo flow quản trị cũ của module này.</p>
                            </div>
                        </div>
                        <div class="settings-action-grid">
                            @foreach ($sectionActions as $action)
                                @php($visibleKey = $action['visible_when'] ?? null)
                                @php($visible = $visibleKey ? (bool) data_get($values, $activeTab . '.' . $visibleKey, $section['fields'][$visibleKey]['default'] ?? false) : true)
                                @if ($visible)
                                    <a class="settings-action-card" href="{{ route($action['route']) }}">
                                        <strong>{{ $action['label'] }}</strong>
                                        <span>Mở màn quản trị module</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (($section['fields'] ?? []) !== [])
                    <div class="form-footer">
                        <button class="btn" type="submit">Lưu {{ $section['label'] }}</button>
                    </div>
                @endif
            </form>
        </section>
    </div>
@endsection
