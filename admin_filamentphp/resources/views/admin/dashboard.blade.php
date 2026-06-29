@extends('admin.layouts.app', ['title' => 'Admin Dashboard'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-description">Tổng quan nhanh hệ thống quản trị Laravel thuần.</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="label">Tổng người dùng</div>
            <div class="value">{{ number_format($totalUsers) }}</div>
        </div>
        <div class="stat">
            <div class="label">Tổng sản phẩm</div>
            <div class="value">{{ number_format($totalProducts) }}</div>
        </div>
        <div class="stat">
            <div class="label">Tổng đơn hàng</div>
            <div class="value">{{ number_format($totalOrders) }}</div>
        </div>
        <div class="stat">
            <div class="label">Doanh thu đơn hàng</div>
            <div class="value">{{ number_format($totalRevenue) }}</div>
        </div>
        <div class="stat">
            <div class="label">Đơn đang xử lý</div>
            <div class="value">{{ number_format($pendingOrders) }}</div>
        </div>
        <div class="stat">
            <div class="label">Thanh toán paid</div>
            <div class="value">{{ number_format($paidPayments) }}</div>
        </div>
        <div class="stat">
            <div class="label">Posts / Pages</div>
            <div class="value">{{ number_format($totalPosts) }} / {{ number_format($totalPages) }}</div>
        </div>
        <div class="stat">
            <div class="label">Media / Tax / Webhooks</div>
            <div class="value">{{ number_format($totalMedia) }} / {{ number_format($totalTaxClasses) }} / {{ number_format($totalWebhooks) }}</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h2 class="section-title">Đơn hàng mới nhất</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mã đơn</th>
                            <th>Trạng thái</th>
                            <th>Tổng tiền</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestOrders as $order)
                            @php($status = $order->status instanceof \BackedEnum ? $order->status->value : $order->status)
                            <tr>
                                <td class="cell-muted">#{{ $order->id }}</td>
                                <td>{{ $order->number ?: '-' }}</td>
                                <td>{{ $status ?: '-' }}</td>
                                <td>{{ number_format($order->total) }} {{ $order->currency }}</td>
                                <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">Chưa có đơn hàng</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2 class="section-title">Thống kê trạng thái</h2>
            <div class="metric-list">
                @forelse ($orderStatusCounts as $status => $total)
                    <div class="metric-row">
                        <span>{{ $status ?: 'unknown' }}</span>
                        <strong>{{ number_format($total) }}</strong>
                    </div>
                @empty
                    <div class="empty-state">Chưa có dữ liệu trạng thái</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
