@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.sidebar.settings') }}</h1>
            <p class="page-description">{{ __('admin.settings.description') }}</p>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="settings-shell">
        <aside class="settings-tabs card">
            @foreach ($schema as $group => $section)
                <a class="settings-tab {{ $activeTab === $group ? 'active' : '' }}" href="{{ route('admin.settings.index', ['tab' => $group]) }}">
                    <span>{{ __($section['label']) }}</span>
                    <small>{{ count($section['fields']) }} {{ __('admin.settings.fields') }}</small>
                </a>
            @endforeach
        </aside>

        <section class="card settings-panel">
                @php($section = $schema[$activeTab])
            <div class="panel-heading settings-heading">
                <div>
                    <h2>{{ __($section['label']) }}</h2>
                    <p>{{ __($section['description'] ?? 'admin.settings.update') }}</p>
                </div>
                <span class="settings-chip">{{ $activeTab }}</span>
            </div>

            <form method="post" action="{{ route('admin.settings.update-group') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="group" value="{{ $activeTab }}">

                @if (($section['fields'] ?? []) !== [])
                    <div class="settings-field-grid">
                        @foreach ($section['fields'] as $name => $field)
                        @php($type = $field['type'] ?? 'text')
                        @php($rawValue = data_get($values, $activeTab . '.' . $name, $field['default'] ?? null))
                        @php($value = is_array($rawValue) ? json_encode($rawValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $rawValue)
                        @php($wide = in_array($type, ['textarea', 'json'], true))
                        @php($currentImage = is_string($rawValue) && $rawValue !== '' ? \Illuminate\Support\Facades\Storage::disk('public')->url($rawValue) : null)

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
                                <input id="setting-{{ $name }}" type="file" name="settings[{{ $name }}]" accept="image/*">
                                @if ($currentImage)
                                    <div class="field-hint" style="margin-top:8px; display:flex; align-items:center; gap:12px;">
                                        <img src="{{ $currentImage }}" alt="{{ __($field['label'] ?? $name) }}" style="width:64px; height:64px; object-fit:contain; border:1px solid #e2e8f0; border-radius:12px; background:#fff; padding:6px;">
                                        <span>{{ $rawValue }}</span>
                                    </div>
                                @endif
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

                @php($sectionActions = $section['management_actions'] ?? [])
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
                                @php($visibleKey = $action['visible_when'] ?? null)
                                @php($visible = $visibleKey ? (bool) data_get($values, $activeTab . '.' . $visibleKey, $section['fields'][$visibleKey]['default'] ?? false) : true)
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
                    <div class="form-footer">
                        <button class="btn" type="submit">{{ __('admin.settings.save_group', ['group' => __($section['label'])]) }}</button>
                    </div>
                @endif
            </form>
        </section>
    </div>
@endsection
