@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.default.title'),
    'description' => $description ?? __('workspace.default.description')
])

@section('kpis')
    <div class="stat">
        <div class="label">Tổng Nhân sự</div>
        <div class="value" style="color: #0f172a;">{{ number_format($metrics['total_agents'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">Đang hoạt động</div>
        <div class="value" style="color: #10b981;">{{ number_format($metrics['active_agents'] ?? 0) }}</div>
    </div>
@endsection

@section('tab_contents')
    <div x-show="activeTab === 0">
        <div class="page-header" style="margin-bottom: 10px;">
            <div>
                <h3 style="margin: 0;">Danh sách Nhân sự</h3>
                <p style="color: #64748b; font-size: 13px; margin-top: 0;">Quản lý các nhân sự / hệ thống thuộc phòng ban này.</p>
            </div>
            <div class="actions">
                <a class="btn" href="#">Thêm Mới</a>
            </div>
        </div>

        <div class="table-panel card">
            <div class="table-wrap">
                <table style="min-width: 800px;">
                    <thead>
                        <tr>
                            <th>Mã NS</th>
                            <th>Tên Nhân sự / Hệ thống</th>
                            <th>Trạng thái</th>
                            <th>{{ __('admin.table_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents ?? [] as $agent)
                        <tr>
                            <td class="cell-muted">{{ $agent->agent_code }}</td>
                            <td><strong>{{ $agent->name }}</strong></td>
                            <td>
                                @if($agent->status === 'active')
                                    <span class="badge badge-success">{{ __('workspace.default.statuses.active') }}</span>
                                @elseif($agent->status === 'suspended')
                                    <span class="badge badge-warning">{{ __('workspace.default.statuses.suspended') }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ __('workspace.default.statuses.inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.department-agents.show', $agent->id) }}" class="link-action">{{ __('admin.actions.view') }}</a>
                                    <a href="{{ route('admin.department-agents.edit', $agent->id) }}" class="link-action">Sửa</a>
                                    <form action="{{ route('admin.department-agents.destroy', $agent->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="link-action link-danger">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="empty-state" style="text-align: center; color: #94a3b8; padding: 20px;">Không có dữ liệu.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
