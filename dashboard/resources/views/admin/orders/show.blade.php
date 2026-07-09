@extends('admin.layouts.app', ['title' => __('admin.order.label') . ' #' . $order->id])

@section('content')
    @php
        $currency = app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class);
        $money = fn ($value) => $currency->format($value, true);
        $statusValue = $order->status instanceof \BackedEnum ? $order->status->value : (string) $order->status;
        $subtotal = $order->subtotal ?? 0;
        $shippingTotal = app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->getTotalShipping($order);
        $taxTotal = app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->getTaxTotal($order);
        $discountTotal = $order->coupons->sum('discount_amount');
    @endphp

    <style>
        .show-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 24px;
        }
        @media (min-width: 1024px) {
            .show-layout {
                grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
                align-items: start;
            }
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .card-header {
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }
        .card-body {
            padding: 16px 24px;
        }
        
        .field-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
        }
        .field-group {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .field-group:last-child {
            border-bottom: none;
        }
        .field-label {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .field-value {
            font-size: 15px;
            color: #1e293b;
            line-height: 1.5;
            word-break: break-word;
        }
        
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; }
        .bg-green { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .bg-red { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .bg-yellow { background: #fef08a; color: #854d0e; border: 1px solid #fde047; }
        
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; color: #0f172a; }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }
        tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 600; }
    </style>

    <div class="actions-bar">
        <div>
            <h1 class="page-title">{{ __('admin.order.label') }} #{{ $order->number }}</h1>
            <p style="margin: 4px 0 0; color: #64748b;">
                {{ $order->customer_display_name }} · {{ $order->currency }} · {{ $order->created_at?->format('Y-m-d H:i') }}
            </p>
        </div>
        <div class="actions" style="display: flex; gap: 12px;">
            <a class="btn btn-secondary" href="{{ route('admin.orders.index') }}" style="background: #fff; border: 1px solid #cbd5e1; color: #475569;">{{ __('admin.actions.back') }}</a>
            <a class="btn btn-primary" href="{{ route('admin.orders.edit', $order->id) }}" style="background: #3b82f6; color: #fff; border: none;">{{ __('admin.actions.edit') }}</a>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message" style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #bbf7d0;">
            {{ session('status') }}
        </div>
    @endif

    <div class="show-layout">
        <!-- Main Column -->
        <div class="show-main">
            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.order.items') }}</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('fields.name') }}</th>
                                <th class="text-right">{{ __('admin.order.unit_price') }}</th>
                                <th class="text-right">{{ __('fields.qty') }}</th>
                                <th class="text-right">{{ __('admin.order.tax') }}</th>
                                <th class="text-right">{{ __('admin.order.total_price') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->items as $item)
                                <tr>
                                    <td>
                                        <div style="display:flex; gap:12px; align-items:center;">
                                            @if ($item->product?->featuredImage?->url)
                                                <img src="{{ $item->product->featuredImage->url }}" alt="" style="width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0;">
                                            @else
                                                <div style="width:48px; height:48px; border-radius:8px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:12px;">No img</div>
                                            @endif
                                            <div>
                                                <div style="font-weight: 500;">{{ $item->name ?? ('Item #' . $item->id) }}</div>
                                                @if ($item->product)
                                                    <div style="color: #64748b; font-size: 13px;">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right">{{ $money($item->unit_price ?? 0) }}</td>
                                    <td class="text-right">x{{ $item->qty ?? 0 }}</td>
                                    <td class="text-right">{{ $money($item->tax?->amount ?? 0) }}</td>
                                    <td class="text-right font-bold">{{ $money($item->total ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" style="text-align: center; color: #94a3b8;">{{ __('admin.messages.empty_state') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payments -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lịch sử thanh toán</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Provider</th>
                                <th>Reference</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Ngày</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->payments as $payment)
                                <tr>
                                    <td><span style="text-transform: uppercase; font-size:12px; font-weight:600;">{{ $payment->method }}</span></td>
                                    <td>
                                        <span class="badge {{ $payment->status === 'paid' ? 'bg-green' : ($payment->status === 'failed' ? 'bg-red' : 'bg-yellow') }}">
                                            {{ $payment->status }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->provider ?: '—' }}</td>
                                    <td>{{ $payment->reference ?: '—' }}</td>
                                    <td class="text-right font-bold">{{ $money($payment->amount) }}</td>
                                    <td class="text-right" style="color:#64748b; font-size:13px;">{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" style="text-align: center; color: #94a3b8;">Chưa có giao dịch thanh toán nào</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Refunds -->
            @if($order->refunds->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lịch sử Hoàn tiền (Refunds)</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Lý do</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Ngày hoàn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->refunds as $refund)
                                <tr>
                                    <td>{{ $refund->reason ?: '—' }}</td>
                                    <td class="text-right font-bold" style="color:#991b1b;">-{{ $money($refund->amount) }}</td>
                                    <td class="text-right" style="color:#64748b; font-size:13px;">{{ $refund->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            
            <!-- Activity Log -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lịch sử trạng thái (Activity Log)</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Hành động</th>
                                <th>Người thực hiện</th>
                                <th class="text-right">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->activities ?? [] as $activity)
                                <tr>
                                    <td>
                                        <div>{{ $activity->description }}</div>
                                        @if($activity->properties->has('attributes') && isset($activity->properties['attributes']['status']))
                                            <div style="font-size:12px; color:#64748b; margin-top:4px;">
                                                Status: {{ $activity->properties['old']['status'] ?? 'N/A' }} &rarr; <strong>{{ $activity->properties['attributes']['status'] }}</strong>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $activity->causer?->name ?? 'System' }}</td>
                                    <td class="text-right" style="color:#64748b; font-size:13px;">{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align: center; color: #94a3b8;">Không có lịch sử</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="show-sidebar">
            
            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tổng quan Đơn hàng</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        <div class="field-group">
                            <div class="field-label">Trạng thái</div>
                            <div class="field-value">
                                <span class="badge" style="background:#e2e8f0; color:#475569; font-size:14px; padding:6px 12px;">{{ strtoupper($statusValue) }}</span>
                            </div>
                        </div>
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <div class="field-label" style="margin:0;">Tạm tính</div>
                            <div class="field-value">{{ $money($subtotal) }}</div>
                        </div>
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <div class="field-label" style="margin:0;">Giảm giá</div>
                            <div class="field-value" style="color:#16a34a;">-{{ $money($discountTotal) }}</div>
                        </div>
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <div class="field-label" style="margin:0;">Thuế (Tax)</div>
                            <div class="field-value">{{ $money($taxTotal) }}</div>
                        </div>
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <div class="field-label" style="margin:0;">Phí vận chuyển</div>
                            <div class="field-value">{{ $money($shippingTotal) }}</div>
                        </div>
                        <div class="field-group" style="display: flex; justify-content: space-between; border-top: 2px solid #e2e8f0; padding-top: 16px; margin-top: 4px;">
                            <div class="field-label" style="margin:0; font-size: 16px; color:#0f172a;">TỔNG CỘNG</div>
                            <div class="field-value font-bold" style="font-size: 20px; color:#2563eb;">{{ $money($order->total) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Khách hàng</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        <div class="field-group">
                            <div class="field-label">Tên hiển thị</div>
                            <div class="field-value">
                                <strong>{{ $order->customer_display_name }}</strong>
                                <span class="badge" style="background:#f1f5f9; color:#64748b; margin-left:8px;">{{ $order->customer_type }}</span>
                            </div>
                        </div>
                        @if($order->user)
                        <div class="field-group">
                            <div class="field-label">Email tài khoản</div>
                            <div class="field-value">{{ $order->user->email }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Giao hàng (Shipping)</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        <div class="field-group">
                            <div class="field-label">Phương thức</div>
                            <div class="field-value">
                                @if($order->shipping?->method)
                                    <span class="badge bg-green">{{ $order->shipping->method }}</span>
                                @else
                                    <span style="color:#94a3b8;">N/A</span>
                                @endif
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field-label">Tracking Number</div>
                            <div class="field-value">{{ $order->shipping?->tracking_number ?: '—' }}</div>
                        </div>
                        <div class="field-group">
                            <div class="field-label">Địa chỉ nhận hàng</div>
                            <div class="field-value">
                                @if($order->shippingAddress)
                                    <div><strong>{{ $order->shippingAddress->first_name }} {{ $order->shippingAddress->last_name }}</strong></div>
                                    <div>{{ $order->shippingAddress->phone }}</div>
                                    @if($order->shippingAddress->email)
                                        <div>{{ $order->shippingAddress->email }}</div>
                                    @endif
                                    <div style="margin-top:8px; color:#475569;">
                                        {{ $order->shippingAddress->address_detail }}<br>
                                        {{ $order->shippingAddress->ward_id ? $order->shippingAddress->ward_id . ', ' : '' }}
                                        {{ $order->shippingAddress->city_id }} {{ $order->shippingAddress->state_id }}
                                    </div>
                                @else
                                    <span style="color:#94a3b8;">Chưa có địa chỉ giao hàng</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thanh toán (Billing)</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        <div class="field-group">
                            <div class="field-label">Địa chỉ thanh toán</div>
                            <div class="field-value">
                                @if($order->billingAddress)
                                    <div><strong>{{ $order->billingAddress->first_name }} {{ $order->billingAddress->last_name }}</strong></div>
                                    <div>{{ $order->billingAddress->phone }}</div>
                                    <div style="margin-top:8px; color:#475569;">
                                        {{ $order->billingAddress->address_detail }}<br>
                                        {{ $order->billingAddress->ward_id ? $order->billingAddress->ward_id . ', ' : '' }}
                                        {{ $order->billingAddress->city_id }} {{ $order->billingAddress->state_id }}
                                    </div>
                                @else
                                    <span style="color:#94a3b8;">Giống địa chỉ giao hàng</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coupons -->
            @if($order->coupons->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Coupons / Khuyến mãi</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        @foreach($order->coupons as $coupon)
                            <div class="field-group">
                                <div class="field-label">Code: <span style="color:#0f172a;">{{ $coupon->coupon_code }}</span></div>
                                <div class="field-value" style="color:#16a34a; font-weight:bold;">
                                    -{{ $money($coupon->discount_amount) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Internal Notes / Meta -->
            @if($order->metas->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ghi chú & Metadata</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        @foreach ($order->metas as $meta)
                            <div class="field-group">
                                <div class="field-label">{{ $meta->key }}</div>
                                <div class="field-value" style="font-size:13px; background:#f8fafc; padding:8px; border-radius:6px; border:1px solid #e2e8f0;">
                                    {{ is_array($meta->value) ? json_encode($meta->value, JSON_UNESCAPED_UNICODE) : $meta->value }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            
        </div>
    </div>
@endsection
