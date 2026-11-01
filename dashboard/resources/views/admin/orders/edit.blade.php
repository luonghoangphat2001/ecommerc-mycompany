@extends('admin.layouts.app', ['title' => __('admin.order.edit_title', ['number' => $order->number])])

@section('content')
    @php
        $statusValue = $order->status instanceof \BackedEnum ? $order->status->value : (string) $order->status;
        $discountTotal = $order->coupons->sum('discount_amount');
        $loyaltyDiscountTotal = $orderService->getLoyaltyDiscountTotal($order);
        $internalNote = $order->metas->where('key', 'internal_note')->first()?->value ?? '';
    @endphp

    <style>
        .item-input { width: 110px; min-height: 30px; padding: 4px 8px; }
        .summary-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed var(--fi-border); }
        .summary-row:last-child { border-bottom: none; }
        .summary-label { font-weight: 500; color: var(--fi-text-secondary); font-size: 12.5px; }
        .summary-value { font-weight: 700; color: var(--fi-text); font-family: var(--fi-font-mono); }
    </style>

    <x-admin.page-header
        :title="__('admin.order.edit_title', ['number' => $order->number])"
        description="Cập nhật danh mục sản phẩm, địa chỉ giao nhận và trạng thái đơn hàng"
        :backUrl="route('admin.orders.show', $order->id)"
    >
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route('admin.orders.show', $order->id) }}">&larr; {{ __('admin.order.cancel_edit') }}</a>
            <button type="submit" form="order-edit-form" class="btn btn-primary">{{ __('admin.order.save_changes') }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="post" action="{{ route('admin.orders.update', $order->id) }}" id="order-edit-form">
        @csrf
        @method('put')

        @if ($errors->any())
            <div style="background: var(--fi-danger-bg); color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; border: 1px solid var(--fi-danger-border);">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="edit-layout">
            <!-- Main Column -->
            <div class="show-main">
                <!-- Order Items -->
                <div class="table-panel card">
                    <div style="padding: 13px 18px; border-bottom: 1px solid var(--fi-border); background: #fcfcfd;">
                        <h3 class="card-title">{{ __('admin.order.order_items') }}</h3>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('admin.order.product') }}</th>
                                    <th>{{ __('admin.order.unit_price') }}</th>
                                    <th>{{ __('admin.order.quantity') }}</th>
                                    <th class="text-right">{{ __('admin.order.line_total') }}</th>
                                </tr>
                            </thead>
                            <tbody id="items-table-body">
                                @foreach ($order->productItems as $index => $item)
                                    <tr class="order-item-row">
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                            <div style="font-weight: 600; color: var(--fi-text);">{{ $item->name ?? ('Item #' . $item->id) }}</div>
                                            @if ($item->product)
                                                <div class="cell-muted mono">{{ __('admin.order.sku') }}: {{ $item->product->sku ?? __('admin.order.not_available') }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" class="item-input js-item-price mono" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}" min="0" step="1">
                                        </td>
                                        <td>
                                            <input type="number" class="item-input js-item-qty mono" name="items[{{ $index }}][qty]" value="{{ $item->qty }}" min="1" step="1">
                                        </td>
                                        <td class="text-right font-bold mono js-item-total-display">
                                            {{ number_format($item->unit_price * $item->qty, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Addresses -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 18px;">
                    <!-- Shipping -->
                    <div class="card" style="margin-bottom: 0;">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('admin.order.shipping_address') }}</h3>
                        </div>
                        <div class="card-body" style="display: grid; gap: 12px;">
                            <div class="form-row">
                                <label class="form-label">{{ __('admin.order.full_name') }}</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" class="form-control" name="shipping[first_name]" value="{{ $order->shippingAddress?->first_name }}" placeholder="Họ">
                                    <input type="text" class="form-control" name="shipping[last_name]" value="{{ $order->shippingAddress?->last_name }}" placeholder="Tên">
                                </div>
                            </div>
                            <div class="form-row" style="display: flex; gap: 8px;">
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.phone') }}</label>
                                    <input type="text" class="form-control" name="shipping[phone]" value="{{ $order->shippingAddress?->phone }}">
                                </div>
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.email') }}</label>
                                    <input type="email" class="form-control" name="shipping[email]" value="{{ $order->shippingAddress?->email }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <label class="form-label">{{ __('admin.order.address_line_1') }}</label>
                                <input type="text" class="form-control" name="shipping[address_detail]" value="{{ $order->shippingAddress?->address_detail }}">
                            </div>
                            <div class="form-row">
                                <label class="form-label">{{ __('admin.order.address_line_2') }}</label>
                                <input type="text" class="form-control" name="shipping[address_line_2]" value="{{ $order->shippingAddress?->address_line_2 }}">
                            </div>
                            <div class="form-row" style="display: flex; gap: 8px;">
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.city') }}</label>
                                    <input type="text" class="form-control" name="shipping[city_id]" value="{{ $order->shippingAddress?->city_id }}">
                                </div>
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.state') }}</label>
                                    <input type="text" class="form-control" name="shipping[state_id]" value="{{ $order->shippingAddress?->state_id }}">
                                </div>
                            </div>
                            <div class="form-row" style="display: flex; gap: 8px;">
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.country') }}</label>
                                    <input type="text" class="form-control" name="shipping[country_code]" value="{{ $order->shippingAddress?->country_code ?? 'VN' }}">
                                </div>
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.postal_code') }}</label>
                                    <input type="text" class="form-control" name="shipping[postal_code]" value="{{ $order->shippingAddress?->postal_code }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing -->
                    <div class="card" style="margin-bottom: 0;">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('admin.order.billing_address') }}</h3>
                        </div>
                        <div class="card-body" style="display: grid; gap: 12px;">
                            <div class="form-row">
                                <label class="form-label">{{ __('admin.order.full_name') }}</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" class="form-control" name="billing[first_name]" value="{{ $order->billingAddress?->first_name }}" placeholder="Họ">
                                    <input type="text" class="form-control" name="billing[last_name]" value="{{ $order->billingAddress?->last_name }}" placeholder="Tên">
                                </div>
                            </div>
                            <div class="form-row" style="display: flex; gap: 8px;">
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.phone') }}</label>
                                    <input type="text" class="form-control" name="billing[phone]" value="{{ $order->billingAddress?->phone }}">
                                </div>
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.email') }}</label>
                                    <input type="email" class="form-control" name="billing[email]" value="{{ $order->billingAddress?->email }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <label class="form-label">{{ __('admin.order.address_line_1') }}</label>
                                <input type="text" class="form-control" name="billing[address_detail]" value="{{ $order->billingAddress?->address_detail }}">
                            </div>
                            <div class="form-row">
                                <label class="form-label">{{ __('admin.order.address_line_2') }}</label>
                                <input type="text" class="form-control" name="billing[address_line_2]" value="{{ $order->billingAddress?->address_line_2 }}">
                            </div>
                            <div class="form-row" style="display: flex; gap: 8px;">
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.city') }}</label>
                                    <input type="text" class="form-control" name="billing[city_id]" value="{{ $order->billingAddress?->city_id }}">
                                </div>
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.state') }}</label>
                                    <input type="text" class="form-control" name="billing[state_id]" value="{{ $order->billingAddress?->state_id }}">
                                </div>
                            </div>
                            <div class="form-row" style="display: flex; gap: 8px;">
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.country') }}</label>
                                    <input type="text" class="form-control" name="billing[country_code]" value="{{ $order->billingAddress?->country_code ?? 'VN' }}">
                                </div>
                                <div style="flex: 1;">
                                    <label class="form-label">{{ __('admin.order.postal_code') }}</label>
                                    <input type="text" class="form-control" name="billing[postal_code]" value="{{ $order->billingAddress?->postal_code }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Internal Note -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin.order.internal_note') }}</h3>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" name="internal_note" placeholder="{{ __('admin.order.internal_note_placeholder') }}">{{ $internalNote }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="show-sidebar">
                <!-- Status -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin.order.status') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <label class="form-label">{{ __('admin.order.order_status') }}</label>
                            <select name="status" class="form-control">
                                @foreach(['pending', 'new', 'processing', 'delivering', 'completed', 'cancelled', 'refunded'] as $s)
                                    <option value="{{ $s }}" @selected($statusValue === $s)>{{ \App\Support\AdminLabel::orderStatus($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Advanced Adjustments -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin.order.adjustments') }}</h3>
                    </div>
                    <div class="card-body" style="display: grid; gap: 12px;">
                        @if($checkoutSettings->enable_shipping)
                        <div class="form-row">
                            <label class="form-label">{{ __('admin.order.shipping_method') }}</label>
                            <select name="shipping_method_id" class="form-control">
                                <option value="">{{ __('admin.order.choose_shipping_method') }}</option>
                                @foreach($shippingMethods as $method)
                                    <option value="{{ $method->id }}" @selected($order->shipping?->shop_shipping_method_id === $method->id)>
                                        {{ $method->name }} ({{ number_format($method->base_fee ?? 0, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if($couponSettings->enable_coupons)
                        <div class="form-row">
                            <label class="form-label">{{ __('admin.order.coupon_code') }}</label>
                            <input type="text" name="coupon_code" class="form-control mono" value="{{ $order->coupons->first()?->coupon_code }}" placeholder="{{ __('admin.order.coupon_code_placeholder') }}">
                        </div>
                        @endif

                        @if($loyaltySettings->enabled)
                        <div class="form-row">
                            <label class="form-label">{{ __('admin.order.redeemed_points') }}</label>
                            @php
                                $redeemedPoints = (int) ($order->metas->where('key', 'redeemed_points')->first()?->value ?? 0);
                            @endphp
                            <input type="number" name="redeemed_points" class="form-control mono" value="{{ $redeemedPoints }}" min="0" step="1">
                        </div>
                        @endif

                        @if($checkoutSettings->enable_tax)
                        <div class="form-row">
                            <label class="form-label">{{ __('admin.order.manual_tax_override') }}</label>
                            @php
                                $manualTaxAmount = $order->metas->where('key', 'manual_tax_amount')->first()?->value;
                            @endphp
                            <input type="number" name="manual_tax_amount" class="form-control mono" value="{{ $manualTaxAmount }}" min="0" step="1" placeholder="{{ __('admin.order.manual_tax_placeholder') }}">
                            <small class="field-hint">{{ __('admin.order.current_system_tax', ['amount' => number_format($order->tax_amount ?? 0, 0, ',', '.')]) }}</small>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Order Calculation -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin.order.cost_calculation') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="summary-row">
                            <span class="summary-label">{{ __('admin.order.subtotal') }}</span>
                            <span class="summary-value" id="js-subtotal-display">{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>

                        @if($checkoutSettings->enable_shipping)
                        <div class="summary-row">
                            <span class="summary-label">{{ __('admin.order.shipping_price') }}</span>
                            <span class="summary-value">{{ number_format($order->shipping?->amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($checkoutSettings->enable_tax)
                        <div class="summary-row">
                            <span class="summary-label">{{ __('admin.order.tax') }}</span>
                            <span class="summary-value">{{ number_format($order->tax_amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($couponSettings->enable_coupons)
                        <div class="summary-row">
                            <span class="summary-label">{{ __('admin.order.discount') }}</span>
                            <span class="summary-value" style="color: #047857;">
                                -{{ number_format($discountTotal, 0, ',', '.') }}
                            </span>
                        </div>
                        @endif

                        @if($loyaltySettings->enabled && $loyaltyDiscountTotal > 0)
                        <div class="summary-row">
                            <span class="summary-label">{{ __('admin.order.redeemed_points') }}</span>
                            <span class="summary-value" style="color: #047857;">
                                -{{ number_format($loyaltyDiscountTotal, 0, ',', '.') }}
                            </span>
                        </div>
                        @endif

                        <div class="summary-row" style="border-top: 2px solid var(--fi-border); margin-top: 8px; padding-top: 14px; flex-direction: column; align-items: flex-end;">
                            <div style="display: flex; justify-content: space-between; width: 100%; align-items: baseline;">
                                <span class="summary-label" style="font-size: 13.5px; font-weight: 700; color: var(--fi-text);">{{ __('admin.order.saved_total') }}</span>
                                <span class="summary-value" style="font-size: 18px; color: var(--fi-primary);">{{ number_format($order->total, 0, ',', '.') }}</span>
                            </div>
                            <span class="field-hint" style="font-style: italic;">
                                {{ __('admin.order.recalculate_note', ['action' => __('admin.order.save_changes')]) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Refund Modal -->
        <div id="refund-modal" style="display: none; position: fixed; inset: 0; background: rgba(9, 30, 52, 0.5); backdrop-filter: blur(2px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
            <div class="card" style="width: min(100%, 420px); margin: 0; box-shadow: var(--fi-shadow-md);">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.order.refund_order_title') }}</h3>
                </div>

                <div style="display: grid; gap: 12px;">
                    <div class="form-row">
                        <label class="form-label">{{ __('admin.order.refund_type') }}</label>
                        <select name="refund_type" id="refund_type" class="form-control" onchange="document.getElementById('refund_amount_container').style.display = this.value === 'partial' ? 'block' : 'none'">
                            <option value="full">{{ \App\Support\AdminLabel::refundType('full') }}</option>
                            <option value="partial">{{ \App\Support\AdminLabel::refundType('partial') }}</option>
                        </select>
                    </div>

                    <div class="form-row" id="refund_amount_container" style="display: none;">
                        <label class="form-label">{{ __('admin.order.refund_amount') }}</label>
                        <input type="number" name="refund_amount" id="refund_amount" class="form-control mono" min="0" step="1" placeholder="{{ __('admin.order.refund_amount_placeholder') }}">
                        <small class="field-hint">{{ __('admin.order.refund_amount_hint') }}</small>
                    </div>

                    <div class="form-row">
                        <label class="form-label">{{ __('admin.order.refund_reason') }}</label>
                        <textarea name="refund_reason" id="refund_reason" class="form-control" placeholder="{{ __('admin.order.refund_reason_placeholder') }}"></textarea>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeRefundModal()">{{ __('admin.order.close') }}</button>
                    <button type="button" class="btn btn-primary" onclick="confirmRefundModal()">{{ __('admin.order.confirm') }}</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formatMoney = (amount) => {
                return new Intl.NumberFormat('vi-VN').format(amount);
            };

            const calculateSubtotals = () => {
                let subtotal = 0;
                document.querySelectorAll('.order-item-row').forEach(row => {
                    const price = parseFloat(row.querySelector('.js-item-price').value) || 0;
                    const qty = parseInt(row.querySelector('.js-item-qty').value) || 0;
                    const itemTotal = price * qty;

                    row.querySelector('.js-item-total-display').innerText = formatMoney(itemTotal);
                    subtotal += itemTotal;
                });

                document.getElementById('js-subtotal-display').innerText = formatMoney(subtotal);
            };

            document.querySelectorAll('.js-item-price, .js-item-qty').forEach(el => {
                el.addEventListener('input', calculateSubtotals);
            });

            const statusSelect = document.querySelector('select[name="status"]');
            const refundModal = document.getElementById('refund-modal');
            let originalStatus = statusSelect.value;

            statusSelect.addEventListener('change', function() {
                if (this.value === 'refunded') {
                    refundModal.style.display = 'flex';
                }
            });

            window.closeRefundModal = function() {
                refundModal.style.display = 'none';
                statusSelect.value = originalStatus;
            };

            window.confirmRefundModal = function() {
                refundModal.style.display = 'none';
                originalStatus = 'refunded';
            };
        });
    </script>
@endsection
