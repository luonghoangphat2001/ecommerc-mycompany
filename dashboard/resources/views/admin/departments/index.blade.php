@extends('admin.layouts.app', ['title' => __('department.index.title')])

@section('content')
    <x-admin.page-header
        :title="__('department.index.title')"
        :description="__('department.index.description')"
        :exportUrl="route('admin.departments.export', request()->query())"
        :importUrl="route('admin.departments.import')"
        :createUrl="route('admin.departments.create')"
        :createLabel="__('department.index.create_btn')"
    />

    @if(session('success'))
        <div class="status-message">{{ session('success') }}</div>
    @endif

    <x-admin.data-table>
        <x-slot:head>
            <tr>
                <th style="width: 120px;">{{ __('department.table.code') }}</th>
                <th>{{ __('department.table.name') }}</th>
                <th style="width: 160px;">{{ __('department.table.risk_level') }}</th>
                <th class="text-center" style="width: 140px;">{{ __('department.table.agents_count') }}</th>
                <th style="width: 150px;">{{ __('department.table.status') }}</th>
                <th class="text-right" style="width: 160px;">{{ __('department.table.actions') }}</th>
            </tr>
        </x-slot:head>

        @foreach($departments as $dept)
            <tr>
                <td class="cell-muted mono" style="font-weight: 700;">{{ strtoupper($dept->code) }}</td>
                <td>
                    <a href="{{ route('admin.departments.show', $dept) }}" style="color: var(--fi-primary); font-weight: 600;">
                        {{ $dept->name }}
                    </a>
                </td>
                <td>
                    <x-admin.status-badge status="secondary" :label="__('department.risk.' . $dept->risk_level_threshold->value)" />
                </td>
                <td class="mono font-bold text-center">{{ $dept->agents_count }}</td>
                <td>
                    <x-admin.status-badge
                        :status="$dept->is_active ? 'active' : 'secondary'"
                        :label="$dept->is_active ? __('department.table.active') : __('department.table.inactive')"
                    />
                </td>
                <td class="text-right">
                    <x-admin.row-actions
                        :viewUrl="route('admin.departments.show', $dept)"
                        :editUrl="route('admin.departments.edit', $dept)"
                    />
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
@endsection
