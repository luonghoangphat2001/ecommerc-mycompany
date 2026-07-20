@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.hr.title'),
    'description' => $description ?? __('workspace.hr.description')
])

@section('kpis')
    <div class="stat">
        <div class="label">{{ __('workspace.hr.kpis.total_agents') }}</div>
        <div class="value" style="color: #10b981;">{{ number_format($metrics['total_agents'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.hr.kpis.active_agents') }}</div>
        <div class="value" style="color: #3b82f6;">{{ number_format($metrics['active_agents'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.hr.kpis.blocked_actions') }}</div>
        <div class="value" style="color: #f59e0b;">{{ number_format($metrics['blocked_actions'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.hr.kpis.risk_level') }}</div>
        <div class="value" style="color: #10b981;">{{ $metrics['risk_level'] ?? 'N/A' }}</div>
    </div>
@endsection

@section('tab_contents')
    <!-- Custom Layout for HR to include a department filter -->
    <div style="margin-bottom: 20px; display: flex; justify-content: flex-start; align-items: center; gap: 10px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <strong style="color: #475569;">Bộ lọc:</strong>
        <select style="padding: 6px 12px; border-radius: 4px; border: 1px solid #cbd5e1; outline: none;">
            <option value="all">Tất cả phòng ban</option>
            <option value="cfo">Tài chính & Kế toán (CFO)</option>
            <option value="logistics">Chuỗi cung ứng (Logistics)</option>
            <option value="ops">Vận hành (Ops)</option>
            <option value="cskh">Chăm sóc khách hàng (CSKH)</option>
        </select>
    </div>

    <div x-show="activeTab === 0">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div>
                <h3 style="margin: 0;">{{ __('workspace.hr.content.contracts_title') }}</h3>
                <p style="color: #64748b; font-size: 13px; margin-top: 0;">{{ __('workspace.hr.content.contracts_desc') }}</p>
            </div>
            <a href="{{ route('admin.employee-contracts.create') }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">+ Thêm Hợp đồng</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('workspace.hr.tables.contracts.code') }}</th>
                        <th>{{ __('workspace.hr.tables.contracts.employee') }}</th>
                        <th>{{ __('workspace.hr.tables.contracts.position') }}</th>
                        <th>{{ __('workspace.hr.tables.contracts.duration') }}</th>
                        <th>{{ __('workspace.hr.tables.contracts.score') }}</th>
                        <th>{{ __('workspace.hr.tables.contracts.status') }}</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                    <tr>
                        <td><strong>{{ $contract->contract_code }}</strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; display: flex; justify-content: center; align-items: center; font-weight: bold; color: #64748b;">
                                    {{ substr($contract->user->name ?? 'N', 0, 1) }}
                                </div>
                                <div>
                                    <strong>{{ $contract->user->name ?? 'N/A' }}</strong><br>
                                    <span style="font-size: 11px; color: #94a3b8;">{{ $contract->department->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $contract->position }}</td>
                        <td>
                            <span style="font-size: 12px; color: #64748b;">{{ $contract->start_date->format('d/m/Y') }}</span>
                            <span style="margin: 0 4px;">-</span>
                            <span style="font-size: 12px; font-weight: bold; color: #0f172a;">{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Vô thời hạn' }}</span>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ $contract->performance_score ?? 0 }}%; background: {{ ($contract->performance_score ?? 0) >= 80 ? '#10b981' : (($contract->performance_score ?? 0) >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                                </div>
                                <span style="font-size: 12px; font-weight: bold;">{{ $contract->performance_score ?? 0 }}</span>
                            </div>
                        </td>
                        <td>
                            @if(!$contract->end_date || $contract->end_date > now())
                                <span class="badge badge-success">{{ __('workspace.hr.statuses.active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ __('workspace.hr.statuses.expired') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.employee-contracts.show', $contract->id) }}" class="link-action">{{ __('admin.actions.view') }}</a>
                                <a href="{{ route('admin.employee-contracts.edit', $contract->id) }}" class="link-action">Sửa</a>
                                <form action="{{ route('admin.employee-contracts.destroy', $contract->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-action link-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8;">{{ __('workspace.hr.tables.empty_contracts') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div x-show="activeTab === 1" style="display: none;">
        <h3>{{ __('workspace.hr.content.health_title') }}</h3>
        <p>{{ __('workspace.hr.content.health_desc') }}</p>
        <div style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
            <p style="color: #94a3b8;">Danh sách tình trạng sức khoẻ AI Agent</p>
        </div>
    </div>

    <div x-show="activeTab === 2" style="display: none;">
        <h3>{{ __('workspace.hr.content.risk_title') }}</h3>
        <p>{{ __('workspace.hr.content.risk_desc') }}</p>
        <div style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
            <p style="color: #94a3b8;">Bảng điều khiển hạn mức và cảnh báo rủi ro AI Agent</p>
        </div>
    </div>
@endsection
