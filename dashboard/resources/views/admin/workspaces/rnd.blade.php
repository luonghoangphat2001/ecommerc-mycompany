@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.rnd.title'),
    'description' => $description ?? __('workspace.rnd.description')
])

@section('kpis')
    <div class="stat card" style="padding: 15px; border-radius: 8px;">
        <div class="label" style="color: #64748b; font-size: 13px;">{{ __('workspace.rnd.kpis.new_products') }}</div>
        <div class="value" style="font-size: 24px; font-weight: bold; color: #0f172a;">{{ number_format($metrics['new_products'] ?? 0) }}</div>
    </div>
    <div class="stat card" style="padding: 15px; border-radius: 8px;">
        <div class="label" style="color: #64748b; font-size: 13px;">{{ __('workspace.rnd.kpis.pending_boms') }}</div>
        <div class="value" style="font-size: 24px; font-weight: bold; color: #f59e0b;">{{ number_format($metrics['pending_boms'] ?? 0) }}</div>
    </div>
    <div class="stat card" style="padding: 15px; border-radius: 8px;">
        <div class="label" style="color: #64748b; font-size: 13px;">{{ __('workspace.rnd.kpis.active_experiments') }}</div>
        <div class="value" style="font-size: 24px; font-weight: bold; color: #3b82f6;">{{ $metrics['active_experiments'] ?? 0 }}</div>
    </div>
    <div class="stat card" style="padding: 15px; border-radius: 8px;">
        <div class="label" style="color: #64748b; font-size: 13px;">{{ __('workspace.rnd.kpis.innovation_index') }}</div>
        <div class="value" style="font-size: 24px; font-weight: bold; color: #10b981;">{{ $metrics['innovation_index'] ?? 'N/A' }}</div>
    </div>
@endsection

@section('tab_contents')
    <!-- Tab 0 -->
    <div x-show="activeTab === 0">
        <h3>{{ __('workspace.rnd.content.bom_title') }}</h3>
        <p>{{ __('workspace.rnd.content.bom_desc') }}</p>
        <div style="height: 300px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
            [{{ __('workspace.rnd.tabs.bom') }}]
        </div>
    </div>

    <!-- Tab 1 -->
    <div x-show="activeTab === 1" style="display: none;">
        <h3>{{ __('workspace.rnd.content.menu_title') }}</h3>
        <p>{{ __('workspace.rnd.content.menu_desc') }}</p>
        <div style="height: 300px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
            [{{ __('workspace.rnd.tabs.menu') }}]
        </div>
    </div>

    <!-- Tab 2 -->
    <div x-show="activeTab === 2" style="display: none;">
        <h3>{{ __('workspace.rnd.content.experiments_title') }}</h3>
        <p>{{ __('workspace.rnd.content.experiments_desc') }}</p>
        <div style="height: 300px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
            [{{ __('workspace.rnd.tabs.experiments') }}]
        </div>
    </div>
@endsection
