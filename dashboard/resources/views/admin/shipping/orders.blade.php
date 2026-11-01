@extends('admin.layouts.app', ['title' => __('admin.sidebar.shipping_orders') ?: 'Đơn vận chuyển'])

@section('content')
    <x-admin.page-header
        :title="'Quản lý Đơn vận chuyển (Shipments & Tracking)'"
        :description="'Theo dõi mã vận đơn, hãng chuyển phát và trạng thái giao hàng của các đơn hàng.'"
    />

    @if (!empty($tabs))
        <div style="margin-bottom: var(--fi-space-4, 16px);">
            <x-admin.tabs
                mode="link"
                :active="$activeTab ?? 'shipping-orders'"
                :tabs="$tabs"
            />
        </div>
    @endif

    <x-admin.data-table :paginator="$items">
        <x-slot:toolbar>
            <x-admin.table-toolbar :resetUrl="route('admin.shipping-orders.index')">
                <x-admin.search-input
                    name="q"
                    :label="__('admin.actions.search')"
                    :placeholder="'Tìm theo mã đơn, mã vận đơn...'"
                    width="260px"
                />
            </x-admin.table-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th style="width: 140px;">Mã đơn hàng</th>
            <th style="width: 180px;">Khách hàng</th>
            <th>Điểm giao hàng</th>
            <th style="width: 200px;">Phương thức / Hãng</th>
            <th style="width: 180px;">Mã vận đơn (Tracking)</th>
            <th style="width: 120px; text-align: right;">Cước phí</th>
            <th style="width: 130px; text-align: center;">Trạng thái</th>
            <th style="width: 100px; text-align: center;">Hành động</th>
        </x-slot:head>

        @forelse ($items as $item)
            @php
                $order = $item->order;
                $address = $order?->shippingAddress;
                $status = $item->metadata['status'] ?? 'processing';
            @endphp
            <tr>
                <td>
                    @if ($order)
                        <a href="{{ route('admin.orders.show', $order->id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;">
                            #{{ $order->number }}
                        </a>
                    @else
                        <span style="color: #94a3b8;">#{{ $item->order_id }}</span>
                    @endif
                </td>
                <td>
                    <div style="font-weight: 600; color: #0f172a;">
                        {{ $address ? trim($address->first_name . ' ' . $address->last_name) : 'Khách vãng lai' }}
                    </div>
                    <div style="font-size: 11px; color: #64748b;">{{ $address?->phone }}</div>
                </td>
                <td>
                    <div style="font-size: 12px; color: #334155;">
                        {{ $address?->address_detail ?: 'N/A' }}
                    </div>
                    <div style="font-size: 11px; color: #64748b;">
                        {{ $address?->city_id ?: ($address?->state_id ?: '') }} {{ $address?->country_code ? '(' . $address->country_code . ')' : '' }}
                    </div>
                </td>
                <td>
                    <div style="font-weight: 600; color: #0f172a; font-size: 13px;">
                        {{ $item->method ?: 'Giao hàng tiêu chuẩn' }}
                    </div>
                    @if (!empty($item->metadata['carrier']))
                        <div style="font-size: 11px; color: #0284c7; font-weight: 600; text-transform: uppercase;">
                            {{ $item->metadata['carrier'] }}
                        </div>
                    @endif
                </td>
                <td>
                    @if ($item->tracking_number)
                        <span style="font-family: monospace; font-size: 12px; font-weight: 600; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #0f172a;">
                            {{ $item->tracking_number }}
                        </span>
                    @else
                        <span style="color: #94a3b8; font-size: 12px;">Chưa tạo mã</span>
                    @endif
                </td>
                <td style="text-align: right; font-weight: 600; font-size: 13px;">
                    {{ number_format($item->amount) }}₫
                </td>
                <td style="text-align: center;">
                    @if ($status === 'delivered' || $status === 'completed')
                        <span style="display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 600; border-radius: 12px; background: #dcfce7; color: #15803d;">Đã giao</span>
                    @elseif ($status === 'delivering' || $status === 'in_transit')
                        <span style="display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 600; border-radius: 12px; background: #e0f2fe; color: #0369a1;">Đang giao</span>
                    @else
                        <span style="display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 600; border-radius: 12px; background: #fef3c7; color: #b45309;">Đang xử lý</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    @if ($item->tracking_number)
                        <button
                            type="button"
                            class="btn btn-secondary btn-sm"
                            style="font-size: 11px; padding: 4px 8px;"
                            onclick="trackShipment('{{ $item->id }}', '{{ $item->tracking_number }}')"
                        >
                            Tra cứu
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 32px; color: #94a3b8;">
                    Chưa có dữ liệu vận chuyển nào.
                </td>
            </tr>
        @endforelse
    </x-admin.data-table>

    <!-- Tracking Modal -->
    <div id="trackingModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Thông tin hành trình vận đơn</h4>
                <button type="button" onclick="closeTrackingModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">&times;</button>
            </div>
            <div id="trackingModalContent">
                <p>Đang tải dữ liệu từ hãng vận chuyển...</p>
            </div>
            <div style="text-align: right; margin-top: 16px;">
                <button type="button" class="btn btn-secondary" onclick="closeTrackingModal()">Đóng</button>
            </div>
        </div>
    </div>

    <script>
        function closeTrackingModal() {
            document.getElementById('trackingModal').style.display = 'none';
        }

        async function trackShipment(id, trackingNumber) {
            const modal = document.getElementById('trackingModal');
            const content = document.getElementById('trackingModalContent');
            modal.style.display = 'flex';
            content.innerHTML = '<p style="text-align: center; color: #64748b;">⏳ Đang tra cứu vận đơn: <b>' + trackingNumber + '</b>...</p>';

            try {
                const response = await fetch('{{ url("admin/shipping-orders") }}/' + id + '/track', {
                    headers: { 'Accept': 'application/json' }
                });
                const res = await response.json();
                if (res.success && res.data) {
                    const d = res.data;
                    let html = '<div style="margin-bottom: 12px; font-size: 13px;">' +
                        '<div>Mã vận đơn: <b>' + d.tracking_number + '</b></div>' +
                        '<div>Đơn vị: <b>' + (d.carrier || 'N/A').toUpperCase() + '</b></div>' +
                        '<div>Trạng thái: <span style="font-weight: 600; color: #0284c7;">' + (d.status_text || d.status) + '</span></div>' +
                        '</div>';

                    if (d.history && d.history.length > 0) {
                        html += '<div style="border-left: 2px solid #0284c7; padding-left: 12px; margin-top: 12px;">';
                        d.history.forEach(function(h) {
                            html += '<div style="margin-bottom: 8px; font-size: 12px;">' +
                                '<div style="color: #64748b; font-size: 11px;">' + (h.time || '') + '</div>' +
                                '<div style="color: #0f172a; font-weight: 500;">' + (h.status || h.desc || JSON.stringify(h)) + '</div>' +
                                '</div>';
                        });
                        html += '</div>';
                    }
                    content.innerHTML = html;
                } else {
                    content.innerHTML = '<p style="color: #dc2626;">' + (res.message || 'Không thể tra cứu thông tin.') + '</p>';
                }
            } catch (e) {
                content.innerHTML = '<p style="color: #dc2626;">Lỗi kết nối khi tra cứu vận đơn.</p>';
            }
        }
    </script>
@endsection
