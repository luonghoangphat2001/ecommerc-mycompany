@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.hr.title'),
    'description' => $description ?? __('workspace.hr.description')
])

@section('kpis')
    <div class="stat">
        <div class="label">{{ __('workspace.hr.kpis.total_agents') }}</div>
        <div class="value" style="color: #047857;">{{ number_format($metrics['total_agents'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.hr.kpis.active_agents') }}</div>
        <div class="value" style="color: var(--fi-primary);">{{ number_format($metrics['active_agents'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.hr.kpis.blocked_actions') }}</div>
        <div class="value" style="color: #b45309;">{{ number_format($metrics['blocked_actions'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.hr.kpis.risk_level') }}</div>
        <div class="value" style="color: #047857;">{{ $metrics['risk_level'] ?? 'N/A' }}</div>
    </div>
@endsection

@section('tab_contents')
    <div x-show="activeTab === 0">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 14px; gap: 12px; flex-wrap: wrap;">
            <div>
                <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.hr.content.contracts_title') }}</h3>
                <p style="color: var(--fi-muted); font-size: 12.5px; margin: 3px 0 0;">{{ __('workspace.hr.content.contracts_desc') }}</p>
            </div>
            <div style="display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap;">
                <x-admin.filter-select
                    name="department_filter"
                    label="Phòng ban"
                    :options="[
                        'all' => 'Tất cả phòng ban',
                        'cfo' => 'Tài chính & Kế toán (CFO)',
                        'logistics' => 'Chuỗi cung ứng (Logistics)',
                        'ops' => 'Vận hành (Ops)',
                        'cskh' => 'Chăm sóc khách hàng (CSKH)',
                    ]"
                    :showPlaceholder="false"
                    width="220px"
                />
                <a href="{{ route('admin.employee-contracts.create') }}" class="btn btn-primary">+ Thêm Hợp đồng</a>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 130px;">{{ __('workspace.hr.tables.contracts.code') }}</th>
                        <th>{{ __('workspace.hr.tables.contracts.employee') }}</th>
                        <th style="width: 170px;">{{ __('workspace.hr.tables.contracts.position') }}</th>
                        <th style="width: 190px;">{{ __('workspace.hr.tables.contracts.duration') }}</th>
                        <th style="width: 150px;">{{ __('workspace.hr.tables.contracts.score') }}</th>
                        <th style="width: 140px;">{{ __('workspace.hr.tables.contracts.status') }}</th>
                        <th class="text-right" style="width: 180px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                        <tr>
                            <td class="mono" style="font-weight: 700;">{{ $contract->contract_code }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 28px; height: 28px; border-radius: 6px; background: var(--fi-primary-soft); display: flex; justify-content: center; align-items: center; font-weight: 700; font-size: 12px; color: var(--fi-primary);">
                                        {{ substr($contract->user->name ?? 'N', 0, 1) }}
                                    </div>
                                    <div>
                                        <strong style="font-weight: 600;">{{ $contract->user->name ?? 'N/A' }}</strong><br>
                                        <span class="cell-muted" style="font-size: 11px;">{{ $contract->department->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $contract->position }}</td>
                            <td class="mono" style="font-size: 12px;">
                                <span class="cell-muted">{{ $contract->start_date->format('d/m/Y') }}</span>
                                <span style="margin: 0 4px;">→</span>
                                <span style="font-weight: 600; color: var(--fi-text);">{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Vô thời hạn' }}</span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                                        <div style="height: 100%; width: {{ $contract->performance_score ?? 0 }}%; background: {{ ($contract->performance_score ?? 0) >= 80 ? '#10b981' : (($contract->performance_score ?? 0) >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                                    </div>
                                    <span class="mono" style="font-size: 12px; font-weight: 700;">{{ $contract->performance_score ?? 0 }}</span>
                                </div>
                            </td>
                            <td>
                                @if(!$contract->end_date || $contract->end_date > now())
                                    <x-admin.status-badge status="active" :label="__('workspace.hr.statuses.active')" />
                                @else
                                    <x-admin.status-badge status="expired" :label="__('workspace.hr.statuses.expired')" />
                                @endif
                            </td>
                            <td class="text-right">
                                <x-admin.row-actions
                                    :viewUrl="route('admin.employee-contracts.show', $contract->id)"
                                    :editUrl="route('admin.employee-contracts.edit', $contract->id)"
                                    :deleteUrl="route('admin.employee-contracts.destroy', $contract->id)"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">{{ __('workspace.hr.tables.empty_contracts') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 1" style="display: none;">
        <div style="margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.hr.content.health_title') }}</h3>
            <p style="margin: 3px 0 0; font-size: 12.5px; color: var(--fi-muted);">{{ __('workspace.hr.content.health_desc') }}</p>
        </div>
        <div class="empty-state" style="border: 1px dashed var(--fi-border-strong); border-radius: var(--fi-radius-md); padding: 48px 20px;">
            Danh sách tình trạng sức khoẻ AI Agent
        </div>
    </div>

    <div x-show="activeTab === 2" style="display: none;">
        <div style="margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.hr.content.risk_title') }}</h3>
            <p style="margin: 3px 0 0; font-size: 12.5px; color: var(--fi-muted);">{{ __('workspace.hr.content.risk_desc') }}</p>
        </div>
        <div class="empty-state" style="border: 1px dashed var(--fi-border-strong); border-radius: var(--fi-radius-md); padding: 48px 20px;">
            Bảng điều khiển hạn mức và cảnh báo rủi ro AI Agent
        </div>
    </div>
@endsection
