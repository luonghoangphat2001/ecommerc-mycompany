@extends('admin.layouts.app', ['title' => __('department.show.title', ['name' => $department->name])])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('department.show.title', ['name' => $department->name]) }}</h1>
            <p class="page-description">{{ __('department.show.description') }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('admin.departments.index') }}">&larr; {{ __('admin.actions.back') }}</a>
        </div>
    </div>

    @if(session('success'))
        <div class="status-message">{{ session('success') }}</div>
    @endif

    @if(session('new_api_token'))
        <div class="card" style="background: var(--fi-info-bg); color: #0369a1; border-color: var(--fi-info-border);">
            <strong>{{ __('department.show.tokens_regenerated') }}</strong> {{ __('department.show.tokens_regenerated_desc') }}<br><br>
            <strong>API Token:</strong> <code>{{ session('new_api_token') }}</code><br>
            <strong>Webhook Secret:</strong> <code>{{ session('new_webhook_secret') }}</code>
        </div>
    @endif

    <div class="table-panel card">
        <div class="table-wrap">
            <table style="min-width: 920px;">
                <thead>
                    <tr>
                        <th>{{ __('department.table.agent_code') }}</th>
                        <th>{{ __('department.table.name') }}</th>
                        <th>{{ __('department.table.status') }}</th>
                        <th>{{ __('department.table.last_active') }}</th>
                        <th class="text-right">{{ __('department.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($department->agents as $agent)
                    <tr>
                        <td class="cell-muted mono">{{ $agent->agent_code }}</td>
                        <td style="font-weight: 600; color: var(--fi-text);">{{ $agent->name }}</td>
                        <td>
                            <span class="badge badge-{{ $agent->status->value === 'active' ? 'success' : 'secondary' }}">
                                {{ __('department.table.' . $agent->status->value) }}
                            </span>
                        </td>
                        <td class="cell-muted mono">{{ $agent->last_active_at ? $agent->last_active_at->diffForHumans() : 'Never' }}</td>
                        <td class="text-right">
                            <form action="{{ route('admin.departments.agents.regenerate', [$department, $agent]) }}" method="POST" onsubmit="return confirm('{{ __('department.show.confirm_regenerate') }}');" style="display:inline; margin:0;">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm">{{ __('department.show.regenerate_tokens') }}</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
