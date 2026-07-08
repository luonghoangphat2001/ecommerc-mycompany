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

    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.order.label') }} #{{ $order->number }}</h1>
            <p class="page-description">
                {{ $order->customer_display_name }} · {{ $order->currency }} · {{ $order->created_at?->format('Y-m-d H:i') }}
            </p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('admin.orders.index') }}">{{ __('admin.actions.back') }}</a>
            <a class="btn" href="{{ route('admin.orders.edit', $order->id) }}">{{ __('admin.actions.edit') }}</a>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="stats">
        <div class="stat">
            <div class="label">{{ __('admin.order.status') }}</div>
            <div class="value" style="font-size:24px;">{{ $statusValue }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.order.subtotal') }}</div>
            <div class="value" style="font-size:24px;">{{ $money($subtotal) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.order.shipping') }}</div>
            <div class="value" style="font-size:24px;">{{ $money($shippingTotal) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.order.total_price') }}</div>
            <div class="value" style="font-size:24px;">{{ $money($order->total) }}</div>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">{{ __('admin.order.status_management') }}</h2>
        <form method="post" action="{{ route('admin.orders.status', $order->id) }}" class="inline-form">
            @csrf
            <div>
                <label for="status">{{ __('admin.order.status') }}</label>
                <select id="status" name="status">
                    @foreach (['pending', 'new', 'processing', 'delivering', 'completed', 'cancelled', 'refunded'] as $status)
                        <option value="{{ $status }}" @selected($statusValue === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn" type="submit">{{ __('admin.actions.save') }}</button>
        </form>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">{{ __('admin.order.details') }}</h2>
        <div class="form-grid">
            <div class="form-row">
                <label>{{ __('admin.order.number') }}</label>
                <div>{{ $order->number }}</div>
            </div>
            <div class="form-row">
                <label>{{ __('admin.order.customer') }}</label>
                <div>{{ $order->customer_display_name }}</div>
            </div>
            <div class="form-row">
                <label>{{ __('admin.order.customer_type') }}</label>
                <div>{{ $order->customer_type }}</div>
            </div>
            <div class="form-row">
                <label>{{ __('admin.order.currency') }}</label>
                <div>{{ $order->currency }}</div>
            </div>
            <div class="form-row">
                <label>{{ __('admin.order.total_tax') }}</label>
                <div>{{ $money($taxTotal) }}</div>
            </div>
            <div class="form-row">
                <label>{{ __('admin.order.discount') }}</label>
                <div>-{{ $money($discountTotal) }}</div>
            </div>
        </div>
    </div>

    <div class="stats" style="margin-top:18px;">
        <div class="stat">
            <div class="label">{{ __('admin.order.payment') }}</div>
            <div class="value" style="font-size:24px;">{{ $order->payments->count() }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.order.refunds') }}</div>
            <div class="value" style="font-size:24px;">{{ $order->refunds->count() }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.order.tax_breakdown') }}</div>
            <div class="value" style="font-size:24px;">{{ $order->taxes->count() }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.order.metadata_notes') }}</div>
            <div class="value" style="font-size:24px;">{{ $order->metas->count() }}</div>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">{{ __('admin.order.contact_info') }}</h2>
        <div class="form-grid">
            <div class="form-row">
                <label>{{ __('admin.order.shipping_address') }}</label>
                <div>
                    <div>{{ $order->shippingAddress?->first_name }} {{ $order->shippingAddress?->last_name }}</div>
                    <div class="cell-muted">{{ $order->shippingAddress?->phone }}</div>
                    <div class="cell-muted">{{ $order->shippingAddress?->email }}</div>
                    <div class="cell-muted">{{ $order->shippingAddress?->address_detail }}</div>
                    <div class="cell-muted">{{ $order->shippingAddress?->city_id }} {{ $order->shippingAddress?->state_id }} {{ $order->shippingAddress?->ward_id }}</div>
                </div>
            </div>
            <div class="form-row">
                <label>{{ __('admin.order.billing_address') }}</label>
                <div>
                    <div>{{ $order->billingAddress?->first_name }} {{ $order->billingAddress?->last_name }}</div>
                    <div class="cell-muted">{{ $order->billingAddress?->phone }}</div>
                    <div class="cell-muted">{{ $order->billingAddress?->email }}</div>
                    <div class="cell-muted">{{ $order->billingAddress?->address_detail }}</div>
                    <div class="cell-muted">{{ $order->billingAddress?->city_id }} {{ $order->billingAddress?->state_id }} {{ $order->billingAddress?->ward_id }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">{{ __('admin.order.shipping') }}</h2>
        <div class="form-grid">
            <div class="form-row">
                <label>{{ __('admin.order.shipping_method') }}</label>
                <div>{{ $order->shipping?->method ?: '—' }}</div>
            </div>
            <div class="form-row">
                <label>{{ __('admin.order.shipping_price') }}</label>
                <div>{{ $money($order->shipping?->amount ?? $shippingTotal) }}</div>
            </div>
            <div class="form-row">
                <label>{{ __('admin.order.tax') }}</label>
                <div>{{ $money($order->shipping?->tax_amount ?? 0) }}</div>
            </div>
            <div class="form-row">
                <label>{{ __('admin.order.tracking_number') }}</label>
                <div>{{ $order->shipping?->tracking_number ?: '—' }}</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">{{ __('admin.order.items') }}</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('fields.name') }}</th>
                        <th>{{ __('fields.qty') }}</th>
                        <th>{{ __('admin.order.unit_price') }}</th>
                        <th>{{ __('admin.order.tax') }}</th>
                        <th>{{ __('admin.order.total_price') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->items as $item)
                        <tr>
                            <td>
                                <div style="display:flex; gap:12px; align-items:center;">
                                    @if ($item->product?->featuredImage?->url)
                                        <img src="{{ $item->product->featuredImage->url }}" alt="" style="width:44px; height:44px; object-fit:cover; border-radius:10px; border:1px solid #e2e8f0;">
                                    @endif
                                    <div>
                                        <div>{{ $item->name ?? ('Item #' . $item->id) }}</div>
                                        @if ($item->product)
                                            <div class="cell-muted">{{ $item->product->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->qty ?? 0 }}</td>
                            <td>{{ $money($item->unit_price ?? 0) }}</td>
                            <td>{{ $money($item->tax?->amount ?? 0) }}</td>
                            <td>{{ $money($item->total ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">{{ __('admin.messages.empty_state') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">{{ __('admin.order.coupons') }}</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('admin.order.code') }}</th>
                        <th>{{ __('admin.order.discount') }}</th>
                        <th>{{ __('admin.common.created_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->coupon_code ?: '—' }}</td>
                            <td>{{ $money($coupon->discount_amount ?? 0) }}</td>
                            <td>{{ $coupon->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">{{ __('admin.messages.empty_state') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">Payments</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Provider</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->payments as $payment)
                        <tr>
                            <td>{{ $payment->method }}</td>
                            <td>{{ $money($payment->amount) }} {{ $payment->currency }}</td>
                            <td>{{ $payment->status }}</td>
                            <td>{{ $payment->provider ?: '—' }}</td>
                            <td>{{ $payment->reference ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">{{ __('admin.messages.empty_state') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="post" action="{{ route('admin.orders.payments.store', $order->id) }}" class="inline-form" style="margin-top:16px;">
            @csrf
            <div><label>Method</label><input type="text" name="method" placeholder="cod, vnpay..." required></div>
            <div><label>Amount</label><input type="number" name="amount" placeholder="amount" min="0" required></div>
            <div><label>Status</label><input type="text" name="status" value="paid" required></div>
            <div><label>Provider</label><input type="text" name="provider" placeholder="provider"></div>
            <div><label>Reference</label><input type="text" name="reference" placeholder="reference"></div>
            <button class="btn" type="submit">Thêm payment</button>
        </form>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">Refunds</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->refunds as $refund)
                        <tr>
                            <td>{{ $money($refund->amount) }}</td>
                            <td>{{ $refund->reason ?: '—' }}</td>
                            <td>{{ $refund->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">{{ __('admin.messages.empty_state') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="post" action="{{ route('admin.orders.refunds.store', $order->id) }}" class="inline-form" style="margin-top:16px;">
            @csrf
            <div><label>Amount</label><input type="number" name="amount" placeholder="amount" min="0" required></div>
            <div><label>Reason</label><input type="text" name="reason" placeholder="reason"></div>
            <button class="btn" type="submit">Tạo refund</button>
        </form>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">Notes / Meta</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->metas as $meta)
                        <tr>
                            <td>{{ $meta->key }}</td>
                            <td style="white-space:normal;">{{ is_array($meta->value) ? json_encode($meta->value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $meta->value }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">{{ __('admin.messages.empty_state') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
