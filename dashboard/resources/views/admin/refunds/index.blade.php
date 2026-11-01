@extends('admin.layouts.app', ['title' => $title ?? 'Quản lý Hoàn tiền'])

@section('content')
    <x-admin.page-header
        :title="$title ?? 'Quản lý Hoàn tiền'"
        description="Lịch sử các giao dịch hoàn tiền cho khách hàng"
        :exportUrl="route('admin.refunds.export', request()->query())"
        :importUrl="route('admin.refunds.import')"
    />

    <x-admin.data-table :paginator="$items">
        <x-slot:toolbar>
            <x-admin.table-toolbar :resetUrl="route($routePrefix . '.index')">
                <x-admin.search-input
                    name="q"
                    label="Tìm kiếm"
                    placeholder="Lý do, số tiền..."
                    width="230px"
                />

                <x-admin.filter-select
                    name="type"
                    label="Loại hoàn tiền"
                    :options="['full' => 'Toàn phần', 'partial' => 'Một phần']"
                    placeholder="-- Tất cả --"
                    width="165px"
                />

                <x-admin.filter-select
                    name="status"
                    label="Trạng thái"
                    :options="['completed' => 'Thành công', 'pending' => 'Đang chờ', 'failed' => 'Thất bại']"
                    placeholder="-- Tất cả --"
                    width="165px"
                />

                <div class="admin-filter-item" style="min-width: 148px; max-width: 165px;">
                    <label for="refund-date-from">Từ ngày</label>
                    <input id="refund-date-from" type="date" name="date_from" value="{{ request('date_from') }}">
                </div>

                <div class="admin-filter-item" style="min-width: 148px; max-width: 165px;">
                    <label for="refund-date-to">Đến ngày</label>
                    <input id="refund-date-to" type="date" name="date_to" value="{{ request('date_to') }}">
                </div>
            </x-admin.table-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th class="text-center" style="width: 72px;">ID</th>
                <th style="width: 130px;">Đơn hàng</th>
                <th>Khách hàng</th>
                <th style="width: 140px;">Loại hoàn tiền</th>
                <th class="text-right" style="width: 150px;">Số tiền</th>
                <th style="width: 140px;">Trạng thái</th>
                <th style="width: 155px;">Ngày tạo</th>
                <th>Lý do</th>
            </tr>
        </x-slot:head>

        @forelse($items as $item)
            @php
                $type = $item->metadata['type'] ?? 'full';
                $status = $item->metadata['status'] ?? 'completed';
            @endphp
            <tr>
                <td class="cell-muted mono text-center">#{{ $item->id }}</td>
                <td>
                    @if($item->order)
                        <a href="{{ route('admin.orders.show', $item->order_id) }}" class="mono" style="font-weight: 700; color: var(--fi-primary);">
                            #{{ $item->order->number ?? $item->order_id }}
                        </a>
                    @else
                        <span class="mono">#{{ $item->order_id }}</span>
                    @endif
                </td>
                <td>
                    @if($item->order && $item->order->user)
                        <strong style="color: var(--fi-text);">{{ $item->order->user->name }}</strong>
                    @else
                        <span class="cell-muted">Khách vãng lai</span>
                    @endif
                </td>
                <td>
                    <x-admin.status-badge :status="$type === 'full' ? 'danger' : 'warning'" :label="$type === 'full' ? 'Toàn phần' : 'Một phần'" />
                </td>
                <td class="text-right font-bold mono" style="color: #b91c1c;">
                    -{{ number_format($item->amount, 0, ',', '.') }}
                </td>
                <td>
                    <x-admin.status-badge
                        :status="$status"
                        :label="$status === 'completed' ? 'Thành công' : ($status === 'failed' ? 'Thất bại' : ucfirst($status))"
                    />
                </td>
                <td class="cell-muted mono">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                <td style="max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->reason }}">
                    {{ $item->reason ?: '—' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="empty-state">Không có dữ liệu hoàn tiền nào.</td>
            </tr>
        @endforelse
    </x-admin.data-table>
@endsection
