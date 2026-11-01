@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.rnd.title'),
    'description' => $description ?? __('workspace.rnd.description')
])

@section('kpis')
    <div class="stat">
        <div class="label">{{ __('workspace.rnd.kpis.new_products') }}</div>
        <div class="value">{{ number_format($metrics['new_products'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.rnd.kpis.pending_boms') }}</div>
        <div class="value" style="color: #b45309;">{{ number_format($metrics['pending_boms'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.rnd.kpis.active_experiments') }}</div>
        <div class="value" style="color: var(--fi-primary);">{{ $metrics['active_experiments'] ?? 0 }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.rnd.kpis.innovation_index') }}</div>
        <div class="value" style="color: #047857;">{{ $metrics['innovation_index'] ?? 'N/A' }}</div>
    </div>
@endsection

@section('tab_contents')
    <div x-show="activeTab === 0">
        <div style="margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.rnd.content.bom_title') }}</h3>
            <p style="margin: 3px 0 0; font-size: 12.5px; color: var(--fi-text-muted);">{{ __('workspace.rnd.content.bom_desc') }}</p>
        </div>
        <div class="empty-state" style="border: 1px dashed var(--fi-border-strong); border-radius: var(--fi-radius-md); padding: 48px 20px;">
            [{{ __('workspace.rnd.tabs.bom') }}]
        </div>
    </div>

    <div x-show="activeTab === 1" style="display: none;">
        <div style="margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.rnd.content.menu_title') }}</h3>
            <p style="margin: 3px 0 0; font-size: 12.5px; color: var(--fi-text-muted);">{{ __('workspace.rnd.content.menu_desc') }}</p>
        </div>
        <div class="empty-state" style="border: 1px dashed var(--fi-border-strong); border-radius: var(--fi-radius-md); padding: 48px 20px;">
            [{{ __('workspace.rnd.tabs.menu') }}]
        </div>
    </div>

    <div x-show="activeTab === 2" style="display: none;">
        <div style="margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.rnd.content.experiments_title') }}</h3>
            <p style="margin: 3px 0 0; font-size: 12.5px; color: var(--fi-text-muted);">{{ __('workspace.rnd.content.experiments_desc') }}</p>
        </div>
        <div class="empty-state" style="border: 1px dashed var(--fi-border-strong); border-radius: var(--fi-radius-md); padding: 48px 20px;">
            [{{ __('workspace.rnd.tabs.experiments') }}]
        </div>
    </div>
@endsection
