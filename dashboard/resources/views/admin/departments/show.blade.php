@extends('admin.layouts.app', ['title' => __('department.show.title', ['name' => $department->name])])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('department.show.title', ['name' => $department->name]) }}</h1>
            <p class="page-description">{{ __('department.show.description') }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('admin.departments.index') }}">{{ __('admin.actions.back') }}</a>
        </div>
    </div>

    @if(session('success'))
        <div class="status-message">{{ session('success') }}</div>
    @endif

    @if(session('new_api_token'))
        <div class="status-message" style="background: #eef2ff; color: #4338ca; border-color: #c7d2fe;">
            <strong>{{ __('department.show.tokens_regenerated') }}</strong> {{ __('department.show.tokens_regenerated_desc') }}<br><br>
            <strong>API Token:</strong> <code>{{ session('new_api_token') }}</code><br>
            <strong>Webhook Secret:</strong> <code>{{ session('new_webhook_secret') }}</code>
        </div>
    @endif

    <div class="table-panel card">
        <div class="table-wrap">
            <table style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>{{ __('department.table.agent_code') }}</th>
                        <th>{{ __('department.table.name') }}</th>
                        <th>{{ __('department.table.status') }}</th>
                        <th>{{ __('department.table.last_active') }}</th>
                        <th>{{ __('department.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($department->agents as $agent)
                    <tr>
                        <td class="cell-muted">{{ $agent->agent_code }}</td>
                        <td>{{ $agent->name }}</td>
                        <td>{{ __('department.table.' . $agent->status->value) }}</td>
                        <td class="cell-muted">{{ $agent->last_active_at ? $agent->last_active_at->diffForHumans() : 'Never' }}</td>
                        <td class="cell-actions">
                            <form action="{{ route('admin.departments.agents.regenerate', [$department, $agent]) }}" method="POST" onsubmit="return confirm('{{ __('department.show.confirm_regenerate') }}');" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary" style="color: #ea580c; border-color: #ea580c;">{{ __('department.show.regenerate_tokens') }}</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
