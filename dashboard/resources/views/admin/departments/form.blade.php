@extends('admin.layouts.app', ['title' => $department->exists ? __('department.form.edit_title', ['name' => $department->name]) : __('department.form.create_title')])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $department->exists ? __('department.form.edit_title', ['name' => $department->name]) : __('department.form.create_title') }}</h1>
            <p class="page-description">{{ __('department.form.description') }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('admin.departments.index') }}">{{ __('admin.actions.back') }}</a>
        </div>
    </div>

    <div class="card">
        <form action="{{ $department->exists ? route('admin.departments.update', $department) : route('admin.departments.store') }}" method="POST">
            @csrf
            @if($department->exists)
                @method('PUT')
            @endif
            
            <div class="form-grid">
                <div class="form-row">
                    <label for="code">{{ __('department.form.code') }}</label>
                    <input type="text" id="code" name="code" value="{{ old('code', $department->code) }}" {{ $department->exists ? 'readonly' : '' }} placeholder="e.g. rnd">
                </div>
                
                <div class="form-row">
                    <label for="name">{{ __('department.form.name') }}</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $department->name) }}" required>
                </div>
                
                <div class="form-row" style="grid-column: 1 / -1;">
                    <label for="description">{{ __('department.form.description_field') }}</label>
                    <textarea id="description" name="description" rows="3">{{ old('description', $department->description) }}</textarea>
                </div>
                
                <div class="form-row">
                    <label for="risk_level_threshold">{{ __('department.form.risk_level_threshold') }}</label>
                    <select id="risk_level_threshold" name="risk_level_threshold">
                        <option value="low" {{ old('risk_level_threshold', $department->risk_level_threshold?->value) == 'low' ? 'selected' : '' }}>{{ __('department.risk.low') }}</option>
                        <option value="medium" {{ old('risk_level_threshold', $department->risk_level_threshold?->value) == 'medium' ? 'selected' : '' }}>{{ __('department.risk.medium') }}</option>
                        <option value="high" {{ old('risk_level_threshold', $department->risk_level_threshold?->value) == 'high' ? 'selected' : '' }}>{{ __('department.risk.high') }}</option>
                        <option value="critical" {{ old('risk_level_threshold', $department->risk_level_threshold?->value) == 'critical' ? 'selected' : '' }}>{{ __('department.risk.critical') }}</option>
                    </select>
                </div>
                
                <div class="form-row" style="grid-column: 1 / -1;">
                    <input type="hidden" name="is_active" value="0">
                    <label class="checkbox-item">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $department->is_active ?? true) ? 'checked' : '' }}>
                        <span>{{ __('department.form.is_active') }}</span>
                    </label>
                </div>
            </div>
            
            <div class="form-actions" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                <button type="submit" class="btn">{{ __('department.form.save') }}</button>
            </div>
        </form>
    </div>
@endsection
