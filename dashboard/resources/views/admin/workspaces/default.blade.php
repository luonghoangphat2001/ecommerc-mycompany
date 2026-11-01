@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.default.title'),
    'description' => $description ?? __('workspace.default.description')
])

@section('kpis')
    <div class="stat">
        <div class="label">Tổng Nhân sự</div>
        <div class="value">{{ number_format($metrics['total_agents'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">Đang hoạt động</div>
        <div class="value" style="color: #047857;">{{ number_format($metrics['active_agents'] ?? 0) }}</div>
    </div>
@endsection

@section('tab_contents')
    <div x-show="activeTab === 0">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 12px; flex-wrap: wrap;">
            <div>
                <h3 style="margin: 0; font-size: 14px; font-weight: 700;">Danh sách Nhân sự</h3>
                <p style="color: var(--fi-muted); font-size: 12.5px; margin: 3px 0 0;">Quản lý các nhân sự / hệ thống thuộc phòng ban này.</p>
            </div>
            <div class="actions">
                <a class="btn btn-primary btn-sm" href="{{ route('admin.department-agents.create') }}">+ Thêm Mới</a>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 140px;">Mã NS</th>
                        <th>Tên Nhân sự / Hệ thống</th>
                        <th style="width: 160px;">Trạng thái</th>
                        <th class="text-right" style="width: 180px;">{{ __('admin.table_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents ?? [] as $agent)
                        @php
                            $agentStatusVal = $agent->status instanceof \BackedEnum ? $agent->status->value : $agent->status;
                        @endphp
                        <tr>
                            <td class="mono cell-muted" style="font-weight: 600;">{{ $agent->agent_code }}</td>
                            <td style="font-weight: 600;">{{ $agent->name }}</td>
                            <td>
                                <x-admin.status-badge
                                    :status="$agentStatusVal"
                                    :label="__('workspace.default.statuses.' . ($agentStatusVal === 'active' ? 'active' : ($agentStatusVal === 'suspended' ? 'suspended' : 'inactive')))"
                                />
                            </td>
                            <td class="text-right">
                                <x-admin.row-actions
                                    :viewUrl="route('admin.department-agents.show', $agent->id)"
                                    :editUrl="route('admin.department-agents.edit', $agent->id)"
                                    :deleteUrl="route('admin.department-agents.destroy', $agent->id)"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">Không có dữ liệu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
