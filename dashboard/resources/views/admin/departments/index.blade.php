@extends('admin.layouts.app', ['title' => __('department.index.title')])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('department.index.title') }}</h1>
            <p class="page-description">{{ __('department.index.description') }}</p>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('admin.departments.create') }}">{{ __('department.index.create_btn') }}</a>
        </div>
    </div>

    @if(session('success'))
        <div class="status-message">{{ session('success') }}</div>
    @endif

    <div class="table-panel card">
        <div class="table-wrap">
            <table style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>{{ __('department.table.code') }}</th>
                        <th>{{ __('department.table.name') }}</th>
                        <th>{{ __('department.table.risk_level') }}</th>
                        <th>{{ __('department.table.agents_count') }}</th>
                        <th>{{ __('department.table.status') }}</th>
                        <th>{{ __('department.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $dept)
                    <tr>
                        <td class="cell-muted">{{ $dept->code }}</td>
                        <td>
                            <a href="{{ route('admin.departments.show', $dept) }}" style="color: var(--primary); font-weight: 500;">
                                {{ $dept->name }}
                            </a>
                        </td>
                        <td>{{ __('department.risk.' . $dept->risk_level_threshold->value) }}</td>
                        <td>{{ $dept->agents_count }}</td>
                        <td>{{ $dept->is_active ? __('department.table.active') : __('department.table.inactive') }}</td>
                        <td>
                            <div class="actions">
                                <a class="link-action" href="{{ route('admin.departments.show', $dept) }}">{{ __('admin.actions.view') }}</a>
                                <a class="link-action" href="{{ route('admin.departments.edit', $dept) }}">{{ __('admin.actions.edit') }}</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
