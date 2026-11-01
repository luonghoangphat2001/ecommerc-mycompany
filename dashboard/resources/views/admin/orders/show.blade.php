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
        $loyaltyDiscountTotal = $orderService->getLoyaltyDiscountTotal($order);
        $internalMetas = ['loyalty_discount', 'redeemed_points'];
        $productImageUrl = function ($product) {
            if (! $product) {
                return null;
            }

            $imagePath = $product->featuredImage?->path;
            $rawImages = $product->product_images;
            $imageId = is_array($rawImages) ? ($rawImages[0] ?? null) : $rawImages;

            if (! $imagePath && is_numeric($imageId)) {
                $imagePath = \Awcodes\Curator\Models\Media::find($imageId)?->path;
            } elseif (! $imagePath && is_string($imageId)) {
                $imagePath = $imageId;
            }

            return $imagePath ? \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath) : null;
        };
    @endphp

    <div class="page-header">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <h1 class="page-title">{{ __('admin.order.label') }} <span class="mono">#{{ $order->number }}</span></h1>
                <span class="badge badge-{{ $statusValue }}">{{ \App\Support\AdminLabel::orderStatus($statusValue) }}</span>
            </div>
            <p class="page-description">
                {{ $order->customer_display_name }} &bull; {{ $order->currency }} &bull; <span class="mono">{{ $order->created_at?->format('Y-m-d H:i') }}</span>
            </p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('admin.orders.index') }}">&larr; {{ __('admin.actions.back') }}</a>
            <a class="btn btn-primary" href="{{ route('admin.orders.edit', $order->id) }}">{{ __('admin.actions.edit') }}</a>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="show-layout">
        <!-- Main Column -->
        <div class="show-main">
            <!-- Order Items -->
            <div class="table-panel card">
                <div style="padding: 13px 18px; border-bottom: 1px solid var(--fi-border); background: #fcfcfd;">
                    <h3 class="card-title">{{ __('admin.order.items') }} ({{ $order->items->count() }})</h3>
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
                                        <div style="display: flex; gap: 12px; align-items: center;">
                                            @php
                                                $imageUrl = $productImageUrl($item->product);
                                            @endphp
                                            @if ($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="" style="width: 42px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid var(--fi-border);">
                                            @else
                                                <div style="width: 42px; height: 42px; border-radius: 6px; background: #f1f5f9; border: 1px solid var(--fi-border); display: grid; place-items: center; color: #94a3b8; font-size: 10.5px;">{{ __('admin.order.no_image') }}</div>
                                            @endif
                                            <div>
                                                <div style="font-weight: 600; color: var(--fi-text);">{{ $item->name ?? ('Item #' . $item->id) }}</div>
                                                @if ($item->product)
                                                    <div class="cell-muted mono">{{ __('admin.order.sku') }}: {{ $item->product->sku ?? __('admin.order.not_available') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right mono">{{ $money($item->unit_price ?? 0) }}</td>
                                    <td class="text-right mono">x{{ $item->qty ?? 0 }}</td>
                                    <td class="text-right mono">{{ $money($item->tax?->amount ?? 0) }}</td>
                                    <td class="text-right font-bold mono">{{ $money($item->total ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="empty-state">{{ __('admin.messages.empty_state') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payments -->
            <div class="table-panel card">
                <div style="padding: 13px 18px; border-bottom: 1px solid var(--fi-border); background: #fcfcfd;">
                    <h3 class="card-title">{{ __('admin.order.payment_history') }}</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('admin.order.method') }}</th>
                                <th>{{ __('admin.order.status') }}</th>
                                <th>{{ __('admin.order.provider') }}</th>
                                <th>{{ __('admin.order.reference') }}</th>
                                <th class="text-right">{{ __('admin.order.amount') }}</th>
                                <th class="text-right">{{ __('admin.order.date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->payments as $payment)
                                <tr>
                                    <td><span class="mono" style="text-transform: uppercase; font-size: 11.5px; font-weight: 700;">{{ $payment->method }}</span></td>
                                    <td>
                                        <span class="badge {{ $payment->status === 'paid' ? 'bg-green' : ($payment->status === 'failed' ? 'bg-red' : 'bg-yellow') }}">
                                            {{ \App\Support\AdminLabel::paymentStatus($payment->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->provider ?: '—' }}</td>
                                    <td class="mono">{{ $payment->reference ?: '—' }}</td>
                                    <td class="text-right font-bold mono">{{ $money($payment->amount) }}</td>
                                    <td class="text-right cell-muted mono">{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="empty-state">{{ __('admin.order.no_payment_history') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Refunds -->
            <div class="table-panel card">
                <div style="padding: 13px 18px; border-bottom: 1px solid var(--fi-border); background: #fcfcfd;">
                    <h3 class="card-title">{{ __('admin.order.refund_history') }}</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('admin.order.type') }}</th>
                                <th>{{ __('admin.order.status') }}</th>
                                <th>{{ __('admin.order.reason') }}</th>
                                <th class="text-right">{{ __('admin.order.amount') }}</th>
                                <th class="text-right">{{ __('admin.order.refund_date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->refunds as $refund)
                                <tr>
                                    <td>
                                        <span class="badge {{ ($refund->metadata['type'] ?? 'full') === 'full' ? 'bg-red' : 'bg-yellow' }}">{{ \App\Support\AdminLabel::refundType($refund->metadata['type'] ?? 'full') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ ($refund->metadata['status'] ?? 'completed') === 'completed' ? 'bg-green' : 'bg-gray' }}">{{ \App\Support\AdminLabel::refundStatus($refund->metadata['status'] ?? 'completed') }}</span>
                                    </td>
                                    <td>{{ $refund->reason ?: '—' }}</td>
                                    <td class="text-right font-bold mono" style="color: #b91c1c;">-{{ $money($refund->amount) }}</td>
                                    <td class="text-right cell-muted mono">{{ $refund->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="empty-state">{{ __('admin.messages.empty_state') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="table-panel card">
                <div style="padding: 13px 18px; border-bottom: 1px solid var(--fi-border); background: #fcfcfd;">
                    <h3 class="card-title">{{ __('admin.order.activity_log') }}</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('admin.order.action') }}</th>
                                <th>{{ __('admin.order.actor') }}</th>
                                <th class="text-right">{{ __('admin.order.time') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->activities ?? [] as $activity)
                                <tr>
                                    <td>
                                        <div style="font-weight: 500; color: var(--fi-text);">{{ $activity->description }}</div>
                                        @if($activity->properties->has('attributes') && isset($activity->properties['attributes']['status']))
                                            <div class="cell-muted" style="margin-top: 3px;">
                                                {{ __('admin.order.status_changed') }}:
                                                {{ \App\Support\AdminLabel::orderStatus($activity->properties['old']['status'] ?? null) }}
                                                &rarr;
                                                <strong style="color: var(--fi-text);">{{ \App\Support\AdminLabel::orderStatus($activity->properties['attributes']['status']) }}</strong>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $activity->causer?->name ?? __('admin.order.system') }}</td>
                                    <td class="text-right cell-muted mono">{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="empty-state">{{ __('admin.order.no_activity_history') }}</td></tr>
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
                    <h3 class="card-title">{{ __('admin.order.order_summary') }}</h3>
                </div>
                <div class="card-body">
                    <div class="field-list tabular-nums">
                        <div class="field-group" style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="field-label" style="margin: 0;">{{ __('admin.order.status') }}</div>
                            <div class="field-value">
                                <span class="badge badge-{{ $statusValue }}">{{ \App\Support\AdminLabel::orderStatus($statusValue) }}</span>
                            </div>
                        </div>
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <div class="field-label" style="margin: 0;">{{ __('admin.order.subtotal') }}</div>
                            <div class="field-value mono">{{ $money($subtotal) }}</div>
                        </div>

                        @if($couponSettings->enable_coupons)
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <div class="field-label" style="margin: 0;">{{ __('admin.order.discount') }}</div>
                            <div class="field-value mono" style="color: #047857;">-{{ $money($discountTotal) }}</div>
                        </div>
                        @endif

                        @if($loyaltySettings->enabled && $loyaltyDiscountTotal > 0)
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <div class="field-label" style="margin: 0;">{{ __('admin.order.redeemed_points') }}</div>
                            <div class="field-value mono" style="color: #047857;">-{{ $money($loyaltyDiscountTotal) }}</div>
                        </div>
                        @endif

                        @if($checkoutSettings->enable_tax)
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <div class="field-label" style="margin: 0;">{{ __('admin.order.tax') }}</div>
                            <div class="field-value mono">{{ $money($taxTotal) }}</div>
                        </div>
                        @endif

                        @if($checkoutSettings->enable_shipping)
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <div class="field-label" style="margin: 0;">{{ __('admin.order.shipping_price') }}</div>
                            <div class="field-value mono">{{ $money($shippingTotal) }}</div>
                        </div>
                        @endif

                        <div class="field-group" style="display: flex; justify-content: space-between; align-items: baseline; border-top: 2px solid var(--fi-border); padding-top: 14px; margin-top: 4px;">
                            <div class="field-label" style="margin: 0; font-size: 13px; color: var(--fi-text);">{{ __('admin.order.grand_total') }}</div>
                            <div class="field-value font-bold mono" style="font-size: 18px; color: var(--fi-primary);">{{ $money($order->total) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.order.customer') }}</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        <div class="field-group">
                            <div class="field-label">{{ __('admin.order.display_name') }}</div>
                            <div class="field-value">
                                <strong>{{ $order->customer_display_name }}</strong>
                                <span class="badge bg-gray" style="margin-left: 6px;">{{ $order->customer_type }}</span>
                            </div>
                        </div>
                        @if($order->user)
                        <div class="field-group">
                            <div class="field-label">{{ __('admin.order.account_email') }}</div>
                            <div class="field-value">{{ $order->user->email }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            @if($checkoutSettings->enable_shipping)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.order.shipping_information') }}</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        <div class="field-group">
                            <div class="field-label">{{ __('admin.order.method') }}</div>
                            <div class="field-value">
                                @if($order->shipping?->method)
                                    <span class="badge bg-green">{{ $order->shipping->method }}</span>
                                @else
                                    <span class="val-empty">{{ __('admin.order.not_available') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field-label">{{ __('admin.order.tracking_number') }}</div>
                            <div class="field-value mono">{{ $order->shipping?->tracking_number ?: '—' }}</div>
                        </div>
                        <div class="field-group">
                            <div class="field-label">{{ __('admin.order.shipping_address') }}</div>
                            <div class="field-value">
                                @if($order->shippingAddress)
                                    <div><strong>{{ $order->shippingAddress->first_name }} {{ $order->shippingAddress->last_name }}</strong></div>
                                    <div class="mono" style="font-size: 12px; color: var(--fi-text-secondary);">{{ $order->shippingAddress->phone }}</div>
                                    @if($order->shippingAddress->email)
                                        <div style="font-size: 12.5px; color: var(--fi-muted);">{{ $order->shippingAddress->email }}</div>
                                    @endif
                                    <div style="margin-top: 6px; color: var(--fi-text-secondary); font-size: 12.5px;">
                                        {{ $order->shippingAddress->address_detail }}<br>
                                        @if($order->shippingAddress->address_line_2)
                                            {{ $order->shippingAddress->address_line_2 }}<br>
                                        @endif
                                        {{ $order->shippingAddress->ward_id }} {{ $order->shippingAddress->city_id }} {{ $order->shippingAddress->state_id }} {{ $order->shippingAddress->postal_code }}<br>
                                        {{ $order->shippingAddress->country_code }}
                                    </div>
                                @else
                                    <span class="val-empty">{{ __('admin.order.shipping_address_empty') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Billing Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.order.billing_information') }}</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        <div class="field-group">
                            <div class="field-label">{{ __('admin.order.billing_address') }}</div>
                            <div class="field-value">
                                @if($order->billingAddress)
                                    <div><strong>{{ $order->billingAddress->first_name }} {{ $order->billingAddress->last_name }}</strong></div>
                                    <div class="mono" style="font-size: 12px; color: var(--fi-text-secondary);">{{ $order->billingAddress->phone }}</div>
                                    @if($order->billingAddress->email)
                                        <div style="font-size: 12.5px; color: var(--fi-muted);">{{ $order->billingAddress->email }}</div>
                                    @endif
                                    <div style="margin-top: 6px; color: var(--fi-text-secondary); font-size: 12.5px;">
                                        {{ $order->billingAddress->address_detail }}<br>
                                        @if($order->billingAddress->address_line_2)
                                            {{ $order->billingAddress->address_line_2 }}<br>
                                        @endif
                                        {{ $order->billingAddress->ward_id }} {{ $order->billingAddress->city_id }} {{ $order->billingAddress->state_id }} {{ $order->billingAddress->postal_code }}<br>
                                        {{ $order->billingAddress->country_code }}
                                    </div>
                                @else
                                    <span class="val-empty">{{ __('admin.order.billing_same_as_shipping') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coupons -->
            @if($couponSettings->enable_coupons && $order->coupons->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.order.coupon_promotions') }}</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        @foreach($order->coupons as $coupon)
                            <div class="field-group" style="display: flex; justify-content: space-between;">
                                <div class="field-label" style="margin: 0;">{{ __('admin.order.code_label') }}: <span class="mono" style="color: var(--fi-text);">{{ $coupon->coupon_code }}</span></div>
                                <div class="field-value mono font-bold" style="color: #047857;">
                                    -{{ $money($coupon->discount_amount) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Internal Notes / Meta -->
            @php
                $displayMetas = $order->metas->filter(fn($m) => !in_array($m->key, $internalMetas));
            @endphp
            @if($displayMetas->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.order.metadata_notes') }}</h3>
                </div>
                <div class="card-body">
                    <div class="field-list">
                        @foreach ($displayMetas as $meta)
                            <div class="field-group">
                                <div class="field-label">{{ $meta->key }}</div>
                                <div class="val-html">
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
