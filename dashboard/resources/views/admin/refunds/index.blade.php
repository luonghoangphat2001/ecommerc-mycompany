@extends('admin.layouts.app', ['title' => $title ?? 'Quản lý Hoàn tiền'])

@section('content')
    <div class="page-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="page-title">{{ $title ?? 'Quản lý Hoàn tiền' }}</h1>
            <p class="page-description">Lịch sử các giao dịch hoàn tiền cho khách hàng</p>
        </div>
    </div>

    <div class="table-panel card">
        <form method="get" style="background: #f8fafc; border-bottom: 1px solid var(--fi-border); padding: 20px; border-radius: 8px 8px 0 0; margin: -22px -22px 22px -22px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <!-- Search -->
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Tìm kiếm chung</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Lý do, số tiền...">
                </div>

                <!-- Refund Type -->
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Loại hoàn tiền</label>
                    <select name="type">
                        <option value="">-- Tất cả --</option>
                        <option value="full" @selected(request('type') === 'full')>Toàn phần</option>
                        <option value="partial" @selected(request('type') === 'partial')>Một phần</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Trạng thái</label>
                    <select name="status">
                        <option value="">-- Tất cả --</option>
                        <option value="completed" @selected(request('status') === 'completed')>Thành công</option>
                        <option value="pending" @selected(request('status') === 'pending')>Đang chờ</option>
                        <option value="failed" @selected(request('status') === 'failed')>Thất bại</option>
                    </select>
                </div>

                <!-- Date range -->
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Từ ngày</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Đến ngày</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary" style="padding: 8px 16px; font-weight: 600; min-height: 38px; color: #475569;">Reset</a>
                <button class="btn btn-primary" type="submit" style="padding: 8px 24px; font-weight: 600; min-height: 38px;">Lọc dữ liệu</button>
            </div>
        </form>

        <div class="table-wrap">
            <table style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Loại hoàn tiền</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Lý do</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $type = $item->metadata['type'] ?? 'full';
                            $status = $item->metadata['status'] ?? 'completed';
                        @endphp
                        <tr>
                            <td><strong>#{{ $item->id }}</strong></td>
                            <td>
                                @if($item->order)
                                    <a href="{{ route('admin.orders.show', $item->order_id) }}" class="link-action">
                                        #{{ $item->order->number ?? $item->order_id }}
                                    </a>
                                @else
                                    #{{ $item->order_id }}
                                @endif
                            </td>
                            <td>
                                @if($item->order && $item->order->user)
                                    {{ $item->order->user->name }}
                                @else
                                    <span class="cell-muted">Khách vãng lai</span>
                                @endif
                            </td>
                            <td>
                                @if($type === 'full')
                                    <span style="display:inline-block; padding:2px 8px; font-size:12px; border-radius:4px; background:#fee2e2; color:#991b1b; font-weight:500;">Toàn phần</span>
                                @else
                                    <span style="display:inline-block; padding:2px 8px; font-size:12px; border-radius:4px; background:#fef08a; color:#854d0e; font-weight:500;">Một phần</span>
                                @endif
                            </td>
                            <td class="font-bold" style="color:#991b1b;">
                                {{ number_format($item->amount, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($status === 'completed')
                                    <span style="display:inline-block; padding:2px 8px; font-size:12px; border-radius:4px; background:#dcfce7; color:#166534; font-weight:500;">Thành công</span>
                                @else
                                    <span style="display:inline-block; padding:2px 8px; font-size:12px; border-radius:4px; background:#f1f5f9; color:#475569; font-weight:500;">{{ ucfirst($status) }}</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                            <td class="max-w-xs" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;" title="{{ $item->reason }}">
                                {{ $item->reason ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state" style="text-align: center; padding: 24px; color: #64748b;">Không có dữ liệu hoàn tiền nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination" style="margin-top: 16px;">
            {{ $items->links('vendor.pagination.admin') ?? $items->links() }}
        </div>
    </div>
@endsection
