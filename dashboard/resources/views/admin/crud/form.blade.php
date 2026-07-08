@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">{{ __('admin.form.description') }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">{{ __('admin.actions.back') }}</a>
        </div>
    </div>

    <div class="card">
        <form method="post" action="{{ $record ? route($routePrefix . '.update', $record->id) : route($routePrefix . '.store') }}">
            @csrf
            @if ($record)
                @method('put')
            @endif

            <div class="form-grid">
                @foreach ($fields as $name => $field)
                    @php($type = $field['type'] ?? 'text')
                    @php($defaultValue = $formData[$name] ?? data_get($record, $name))
                    @php($displayValue = is_array($defaultValue) ? json_encode($defaultValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $defaultValue)
                    @php($isWide = in_array($type, ['textarea', 'multiselect', 'checkboxgroup'], true))

                    <div class="form-row {{ $isWide ? 'form-row-wide' : '' }}">
                        <label for="{{ $name }}">{{ $field['label'] ?? $name }}</label>

                        @if ($type === 'textarea')
                            <textarea id="{{ $name }}" name="{{ $name }}" rows="6">{{ old($name, $displayValue) }}</textarea>
                        @elseif ($type === 'select')
                            <select id="{{ $name }}" name="{{ $name }}">
                                @foreach (($field['options'] ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected((string) old($name, $displayValue) === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'multiselect')
                            @php($selected = old($name, $formData[$name] ?? []))
                            <select id="{{ $name }}" name="{{ $name }}[]" multiple size="7">
                                @foreach (($field['options'] ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected(in_array((string) $value, array_map('strval', (array) $selected), true))>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'checkboxgroup')
                            @php($selected = old($name, $formData[$name] ?? []))
                            <div class="checkbox-grid" id="{{ $name }}">
                                @foreach (($field['options'] ?? []) as $value => $label)
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" @checked(in_array((string) $value, array_map('strval', (array) $selected), true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $displayValue) }}">
                        @endif

                        @if (!empty($field['hint']))
                            <small class="field-hint">{{ $field['hint'] }}</small>
                        @endif

                        @error($name)
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="form-footer">
                <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">{{ __('admin.actions.cancel') }}</a>
                <button class="btn" type="submit">{{ __('admin.actions.save') }}</button>
            </div>
        </form>
    </div>
@endsection
