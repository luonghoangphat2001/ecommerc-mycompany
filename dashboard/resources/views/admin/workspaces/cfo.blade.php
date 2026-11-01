@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.cfo.title'),
    'description' => $description ?? __('workspace.cfo.description')
])

@section('workspace_filters')
    <div class="table-panel card" style="margin-bottom: 18px;">
        <x-admin.table-toolbar
            :action="route('admin.workspace.show', 'cfo')"
            :resetUrl="route('admin.workspace.show', 'cfo')"
        >
            <x-admin.filter-select
                name="period"
                :label="__('workspace.cfo.filters.label')"
                :options="[
                    'all' => __('workspace.cfo.filters.all'),
                    'month' => __('workspace.cfo.filters.month'),
                    'quarter' => __('workspace.cfo.filters.quarter'),
                    'year' => __('workspace.cfo.filters.year'),
                ]"
                :showPlaceholder="false"
                width="210px"
            />
        </x-admin.table-toolbar>
    </div>
@endsection

@section('kpis')
    <div class="stat">
        <div class="label">{{ __('workspace.cfo.kpis.revenue') }}</div>
        <div class="value" style="color: #047857;">{{ number_format($metrics['revenue'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.cfo.kpis.expenses') }}</div>
        <div class="value" style="color: #b91c1c;">{{ number_format($metrics['expenses'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.cfo.kpis.cashflow_status') }}</div>
        <div class="value" style="color: var(--fi-primary);">{{ number_format($metrics['cashflow'] ?? 0) }}</div>
    </div>
    <div class="stat" style="{{ ($metrics['urgent_proposals'] ?? 0) > 0 ? 'border-color: #fecaca; background: #fef2f2;' : '' }}">
        <div class="label">{{ __('workspace.cfo.kpis.pending_payments') }}</div>
        <div class="value" style="color: {{ ($metrics['urgent_proposals'] ?? 0) > 0 ? '#b91c1c' : '#b45309' }};">
            {{ $metrics['pending_proposals'] ?? 0 }}
            @if(($metrics['urgent_proposals'] ?? 0) > 0)
                <x-admin.status-badge status="danger" :label="__('workspace.cfo.statuses.urgent_alert')" style="margin-left: 6px; vertical-align: middle;" />
            @endif
        </div>
    </div>
@endsection

@section('tab_contents')
    <!-- Tab 0: Duyệt Chi -->
    <div x-show="activeTab === 0">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 12px; flex-wrap: wrap;">
            <div>
                <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.cfo.tabs.approvals') }}</h3>
                <p style="margin: 3px 0 0; font-size: 12.5px; color: var(--fi-muted);">{{ __('workspace.cfo.content.approvals_desc') }}</p>
            </div>
            <a href="{{ route('admin.financial-proposals.create') }}" class="btn btn-primary btn-sm">+ Thêm Đề xuất</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('workspace.cfo.tables.approvals.title') }}</th>
                        <th style="width: 180px;">{{ __('workspace.cfo.tables.approvals.proposer') }}</th>
                        <th class="text-right" style="width: 160px;">{{ __('workspace.cfo.tables.approvals.amount') }}</th>
                        <th style="width: 140px;">{{ __('workspace.cfo.tables.approvals.status') }}</th>
                        <th style="width: 130px;">{{ __('workspace.cfo.tables.approvals.urgent') }}</th>
                        <th class="text-right" style="width: 180px;">{{ __('workspace.cfo.tables.approvals.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposals as $proposal)
                        <tr>
                            <td style="font-weight: 600;">{{ $proposal->title }}</td>
                            <td>{{ $proposal->user->name ?? 'N/A' }}</td>
                            <td class="mono text-right" style="font-weight: 700;">{{ number_format($proposal->amount) }}</td>
                            <td>
                                <x-admin.status-badge
                                    :status="$proposal->status"
                                    :label="__('workspace.cfo.statuses.' . ($proposal->status === 'pending' ? 'pending' : ($proposal->status === 'approved' ? 'approved' : 'rejected')))"
                                />
                            </td>
                            <td>
                                @if($proposal->is_urgent)
                                    <x-admin.status-badge status="danger" :label="__('workspace.cfo.statuses.urgent_yes')" />
                                @else
                                    <span class="cell-muted">{{ __('workspace.cfo.statuses.urgent_no') }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <x-admin.row-actions
                                    :viewUrl="route('admin.financial-proposals.show', $proposal->id)"
                                    :editUrl="route('admin.financial-proposals.edit', $proposal->id)"
                                    :deleteUrl="route('admin.financial-proposals.destroy', $proposal->id)"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">{{ __('workspace.cfo.tables.empty_proposals') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 1: Kế toán & Bảng lương -->
    <div x-show="activeTab === 1" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 12px; flex-wrap: wrap;">
            <div>
                <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.cfo.tabs.cashflow') }}</h3>
                <p style="margin: 3px 0 0; font-size: 12.5px; color: var(--fi-muted);">{{ __('workspace.cfo.content.cashflow_desc') }}</p>
            </div>
            <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary btn-sm">+ Thêm Bảng lương</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 120px;">{{ __('workspace.cfo.tables.payrolls.period') }}</th>
                        <th>{{ __('workspace.cfo.tables.payrolls.employee') }}</th>
                        <th class="text-right" style="width: 150px;">{{ __('workspace.cfo.tables.payrolls.base_salary') }}</th>
                        <th class="text-right" style="width: 140px;">{{ __('workspace.cfo.tables.payrolls.allowance') }}</th>
                        <th class="text-right" style="width: 140px;">{{ __('workspace.cfo.tables.payrolls.tax_ins') }}</th>
                        <th class="text-right" style="width: 150px;">{{ __('workspace.cfo.tables.payrolls.net_salary') }}</th>
                        <th class="text-right" style="width: 180px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                        <tr>
                            <td class="mono" style="font-weight: 600;">{{ $payroll->month }}</td>
                            <td style="font-weight: 600;">{{ $payroll->user->name ?? 'N/A' }}</td>
                            <td class="mono text-right">{{ number_format($payroll->base_salary) }}</td>
                            <td class="mono text-right">{{ number_format($payroll->allowance) }}</td>
                            <td class="mono text-right" style="color: #b91c1c;">-{{ number_format($payroll->tax + $payroll->insurance) }}</td>
                            <td class="mono text-right" style="font-weight: 700; color: #047857;">{{ number_format($payroll->net_salary) }}</td>
                            <td class="text-right">
                                <x-admin.row-actions
                                    :viewUrl="route('admin.payrolls.show', $payroll->id)"
                                    :editUrl="route('admin.payrolls.edit', $payroll->id)"
                                    :deleteUrl="route('admin.payrolls.destroy', $payroll->id)"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">{{ __('workspace.cfo.tables.empty_payrolls') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: Giá Nguyên vật liệu -->
    <div x-show="activeTab === 2" style="display: none;">
        <div style="margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.cfo.tabs.pricing') }}</h3>
            <p style="margin: 3px 0 0; font-size: 12.5px; color: var(--fi-muted);">{{ __('workspace.cfo.content.pricing_desc') }}</p>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('workspace.cfo.tables.pricing.material') }}</th>
                        <th class="text-right" style="width: 200px;">{{ __('workspace.cfo.tables.pricing.price') }}</th>
                        <th style="width: 180px;">{{ __('workspace.cfo.tables.pricing.date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prices as $price)
                        <tr>
                            <td style="font-weight: 600;">{{ $price->material_name }}</td>
                            <td class="mono text-right" style="color: #b45309; font-weight: 700;">{{ number_format($price->price) }}</td>
                            <td class="mono cell-muted">{{ $price->recorded_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">{{ __('workspace.cfo.tables.empty_pricing') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
