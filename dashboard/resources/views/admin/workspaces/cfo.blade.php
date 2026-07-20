@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.cfo.title'),
    'description' => $description ?? __('workspace.cfo.description')
])

@section('workspace_filters')
    <div class="table-panel card workspace-filter-card">
        <form method="GET" action="{{ route('admin.workspace.show', 'cfo') }}">
            <div class="list-filter-panel">
                <div class="list-filter-grid">
                    <div>
                        <label>{{ __('workspace.cfo.filters.label') }}</label>
                        <select name="period">
                            <option value="all" {{ request('period') === 'all' ? 'selected' : '' }}>{{ __('workspace.cfo.filters.all') }}</option>
                            <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>{{ __('workspace.cfo.filters.month') }}</option>
                            <option value="quarter" {{ request('period') === 'quarter' ? 'selected' : '' }}>{{ __('workspace.cfo.filters.quarter') }}</option>
                            <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>{{ __('workspace.cfo.filters.year') }}</option>
                        </select>
                    </div>
                </div>
                <div class="list-filter-actions">
                    <a href="{{ route('admin.workspace.show', 'cfo') }}" class="btn btn-secondary">{{ __('admin.actions.reset') }}</a>
                    <button class="btn btn-primary" type="submit">{{ __('admin.actions.filter_data') }}</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('kpis')
    <!-- KPIs -->
    <div class="stat">
        <div class="label">{{ __('workspace.cfo.kpis.revenue') }}</div>
        <div class="value" style="color: #10b981;">{{ number_format($metrics['revenue'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.cfo.kpis.expenses') }}</div>
        <div class="value" style="color: #ef4444;">{{ number_format($metrics['expenses'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.cfo.kpis.cashflow_status') }}</div>
        <div class="value" style="color: #3b82f6;">{{ number_format($metrics['cashflow'] ?? 0) }}</div>
    </div>
    <div class="stat" style="{{ ($metrics['urgent_proposals'] ?? 0) > 0 ? 'border-color: #ef4444; background: #fef2f2;' : '' }}">
        <div class="label">{{ __('workspace.cfo.kpis.pending_payments') }}</div>
        <div class="value" style="color: {{ ($metrics['urgent_proposals'] ?? 0) > 0 ? '#ef4444' : '#f59e0b' }};">
            {{ $metrics['pending_proposals'] ?? 0 }}
            @if(($metrics['urgent_proposals'] ?? 0) > 0)
                <span style="font-size: 12px; font-weight: normal; margin-left: 5px;">{{ __('workspace.cfo.statuses.urgent_alert') }}</span>
            @endif
        </div>
    </div>
@endsection

@section('tab_contents')
    <!-- Tab 0: Duyệt Chi -->
    <div x-show="activeTab === 0">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h3>{{ __('workspace.cfo.tabs.approvals') }}</h3>
            <a href="{{ route('admin.financial-proposals.create') }}" class="btn btn-primary" style="padding: 5px 15px;">+ Thêm Đề xuất</a>
        </div>
        <p>{{ __('workspace.cfo.content.approvals_desc') }}</p>
        
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('workspace.cfo.tables.approvals.title') }}</th>
                        <th>{{ __('workspace.cfo.tables.approvals.proposer') }}</th>
                        <th>{{ __('workspace.cfo.tables.approvals.amount') }}</th>
                        <th>{{ __('workspace.cfo.tables.approvals.status') }}</th>
                        <th>{{ __('workspace.cfo.tables.approvals.urgent') }}</th>
                        <th>{{ __('workspace.cfo.tables.approvals.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposals as $proposal)
                    <tr>
                        <td>{{ $proposal->title }}</td>
                        <td>{{ $proposal->user->name ?? 'N/A' }}</td>
                        <td style="font-weight: bold;">{{ number_format($proposal->amount) }}</td>
                        <td>
                            @if($proposal->status === 'pending')
                                <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 12px; font-size: 12px;">{{ __('workspace.cfo.statuses.pending') }}</span>
                            @elseif($proposal->status === 'approved')
                                <span style="background: #d1fae5; color: #047857; padding: 3px 8px; border-radius: 12px; font-size: 12px;">{{ __('workspace.cfo.statuses.approved') }}</span>
                            @else
                                <span style="background: #fee2e2; color: #b91c1c; padding: 3px 8px; border-radius: 12px; font-size: 12px;">{{ __('workspace.cfo.statuses.rejected') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($proposal->is_urgent)
                                <span style="color: #ef4444; font-weight: bold;">{{ __('workspace.cfo.statuses.urgent_yes') }}</span>
                            @else
                                <span style="color: #94a3b8;">{{ __('workspace.cfo.statuses.urgent_no') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($proposal->status === 'pending')
                                <span class="badge badge-warning">{{ __('workspace.cfo.statuses.pending') }}</span>
                            @else
                                <span class="badge badge-success">{{ __('workspace.cfo.statuses.processed') }}</span>
                            @endif
                            <div class="actions">
                                <a href="{{ route('admin.financial-proposals.show', $proposal->id) }}" class="link-action">{{ __('admin.actions.view') }}</a>
                                <a href="{{ route('admin.financial-proposals.edit', $proposal->id) }}" class="link-action">Sửa</a>
                                <form action="{{ route('admin.financial-proposals.destroy', $proposal->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-action link-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8;">{{ __('workspace.cfo.tables.empty_proposals') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 1: Kế toán & Bảng lương -->
    <div x-show="activeTab === 1" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h3>{{ __('workspace.cfo.tabs.cashflow') }}</h3>
            <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary" style="padding: 5px 15px;">+ Thêm Bảng lương</a>
        </div>
        <p>{{ __('workspace.cfo.content.cashflow_desc') }}</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('workspace.cfo.tables.payrolls.period') }}</th>
                        <th>{{ __('workspace.cfo.tables.payrolls.employee') }}</th>
                        <th>{{ __('workspace.cfo.tables.payrolls.base_salary') }}</th>
                        <th>{{ __('workspace.cfo.tables.payrolls.allowance') }}</th>
                        <th>{{ __('workspace.cfo.tables.payrolls.tax_ins') }}</th>
                        <th>{{ __('workspace.cfo.tables.payrolls.net_salary') }}</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                    <tr>
                        <td>{{ $payroll->month }}</td>
                        <td>{{ $payroll->user->name ?? 'N/A' }}</td>
                        <td>{{ number_format($payroll->base_salary) }}</td>
                        <td>{{ number_format($payroll->allowance) }}</td>
                        <td style="color: #ef4444;">-{{ number_format($payroll->tax + $payroll->insurance) }}</td>
                        <td style="font-weight: bold; color: #10b981;">{{ number_format($payroll->net_salary) }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.payrolls.show', $payroll->id) }}" class="link-action">{{ __('admin.actions.view') }}</a>
                                <a href="{{ route('admin.payrolls.edit', $payroll->id) }}" class="link-action">Sửa</a>
                                <form action="{{ route('admin.payrolls.destroy', $payroll->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-action link-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8;">{{ __('workspace.cfo.tables.empty_payrolls') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: Giá Nguyên vật liệu -->
    <div x-show="activeTab === 2" style="display: none;">
        <h3>{{ __('workspace.cfo.tabs.pricing') }}</h3>
        <p>{{ __('workspace.cfo.content.pricing_desc') }}</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('workspace.cfo.tables.pricing.material') }}</th>
                        <th>{{ __('workspace.cfo.tables.pricing.price') }}</th>
                        <th>{{ __('workspace.cfo.tables.pricing.date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prices as $price)
                    <tr>
                        <td style="font-weight: bold;">{{ $price->material_name }}</td>
                        <td style="color: #f59e0b; font-weight: bold;">{{ number_format($price->price) }}</td>
                        <td>{{ $price->recorded_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #94a3b8;">{{ __('workspace.cfo.tables.empty_pricing') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
