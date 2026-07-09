@extends('admin.layouts.app', ['title' => 'Chỉnh sửa đơn hàng #' . $order->number])

@section('content')
    @php
        $statusValue = $order->status instanceof \BackedEnum ? $order->status->value : (string) $order->status;
        $discountTotal = $order->coupons->sum('discount_amount');
        $loyaltyDiscountTotal = $orderService->getLoyaltyDiscountTotal($order);
        $internalNote = $order->metas->where('key', 'internal_note')->first()?->value ?? '';
    @endphp

    <style>
        .edit-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 24px;
        }
        @media (min-width: 1024px) {
            .edit-layout {
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
        }
        .card-title { margin: 0; font-size: 16px; font-weight: 600; color: #0f172a; }
        .card-body { padding: 16px 24px; }
        
        .form-row { margin-bottom: 16px; }
        .form-row:last-child { margin-bottom: 0; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        .form-control { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; color: #1e293b; background: #fff; transition: border-color 0.2s; box-sizing: border-box; }
        .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        textarea.form-control { min-height: 80px; resize: vertical; }
        
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; vertical-align: middle; }
        
        .item-input { width: 100px; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 600; }
        
        .summary-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed #e2e8f0; }
        .summary-row:last-child { border-bottom: none; }
        .summary-label { font-weight: 500; color: #475569; }
        .summary-value { font-weight: 600; color: #0f172a; }
        
        .actions-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; color: #0f172a; }
    </style>

    <form method="post" action="{{ route('admin.orders.update', $order->id) }}" id="order-edit-form">
        @csrf
        @method('put')

        <div class="actions-bar">
            <div>
                <h1 class="page-title">Chỉnh sửa Đơn hàng #{{ $order->number }}</h1>
            </div>
            <div class="actions" style="display: flex; gap: 12px;">
                <a class="btn btn-secondary" href="{{ route('admin.orders.show', $order->id) }}" style="background: #fff; border: 1px solid #cbd5e1; color: #475569;">Hủy</a>
                <button type="submit" class="btn btn-primary" style="background: #3b82f6; color: #fff; border: none;">Lưu thay đổi</button>
            </div>
        </div>

        @if ($errors->any())
            <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #fecaca;">
                <ul style="margin:0; padding-left:20px;">
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
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sản phẩm trong đơn hàng</h3>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th class="text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="items-table-body">
                                @foreach ($order->productItems as $index => $item)
                                    <tr class="order-item-row">
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                            <div style="font-weight: 500;">{{ $item->name ?? ('Item #' . $item->id) }}</div>
                                            @if ($item->product)
                                                <div style="color: #64748b; font-size: 13px;">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" class="item-input js-item-price" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}" min="0" step="1">
                                        </td>
                                        <td>
                                            <input type="number" class="item-input js-item-qty" name="items[{{ $index }}][qty]" value="{{ $item->qty }}" min="1" step="1">
                                        </td>
                                        <td class="text-right font-bold js-item-total-display">
                                            {{ number_format($item->unit_price * $item->qty, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Addresses -->
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <!-- Shipping -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Địa chỉ giao hàng (Shipping)</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <label class="form-label">Họ Tên</label>
                                <div style="display:flex; gap:8px;">
                                    <input type="text" class="form-control" name="shipping[first_name]" value="{{ $order->shippingAddress?->first_name }}" placeholder="Họ">
                                    <input type="text" class="form-control" name="shipping[last_name]" value="{{ $order->shippingAddress?->last_name }}" placeholder="Tên">
                                </div>
                            </div>
                            <div class="form-row" style="display:flex; gap:8px;">
                                <div style="flex:1;">
                                    <label class="form-label">SĐT</label>
                                    <input type="text" class="form-control" name="shipping[phone]" value="{{ $order->shippingAddress?->phone }}">
                                </div>
                                <div style="flex:1;">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="shipping[email]" value="{{ $order->shippingAddress?->email }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Địa chỉ 1 (Address Line 1)</label>
                                <input type="text" class="form-control" name="shipping[address_detail]" value="{{ $order->shippingAddress?->address_detail }}" placeholder="Số nhà, Tên đường...">
                            </div>
                            <div class="form-row">
                                <label class="form-label">Địa chỉ 2 (Address Line 2)</label>
                                <input type="text" class="form-control" name="shipping[address_line_2]" value="{{ $order->shippingAddress?->address_line_2 }}" placeholder="Tòa nhà, Khu phố...">
                            </div>
                            <div class="form-row" style="display:flex; gap:8px;">
                                <div style="flex:1;">
                                    <label class="form-label">Thành phố (City)</label>
                                    <input type="text" class="form-control" name="shipping[city_id]" value="{{ $order->shippingAddress?->city_id }}">
                                </div>
                                <div style="flex:1;">
                                    <label class="form-label">Tỉnh / Bang (State)</label>
                                    <input type="text" class="form-control" name="shipping[state_id]" value="{{ $order->shippingAddress?->state_id }}">
                                </div>
                            </div>
                            <div class="form-row" style="display:flex; gap:8px;">
                                <div style="flex:1;">
                                    <label class="form-label">Quốc gia (Country)</label>
                                    <input type="text" class="form-control" name="shipping[country_code]" value="{{ $order->shippingAddress?->country_code ?? 'VN' }}">
                                </div>
                                <div style="flex:1;">
                                    <label class="form-label">Mã bưu điện (Zip)</label>
                                    <input type="text" class="form-control" name="shipping[postal_code]" value="{{ $order->shippingAddress?->postal_code }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Địa chỉ thanh toán (Billing)</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <label class="form-label">Họ Tên</label>
                                <div style="display:flex; gap:8px;">
                                    <input type="text" class="form-control" name="billing[first_name]" value="{{ $order->billingAddress?->first_name }}" placeholder="Họ">
                                    <input type="text" class="form-control" name="billing[last_name]" value="{{ $order->billingAddress?->last_name }}" placeholder="Tên">
                                </div>
                            </div>
                            <div class="form-row" style="display:flex; gap:8px;">
                                <div style="flex:1;">
                                    <label class="form-label">SĐT</label>
                                    <input type="text" class="form-control" name="billing[phone]" value="{{ $order->billingAddress?->phone }}">
                                </div>
                                <div style="flex:1;">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="billing[email]" value="{{ $order->billingAddress?->email }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Địa chỉ 1 (Address Line 1)</label>
                                <input type="text" class="form-control" name="billing[address_detail]" value="{{ $order->billingAddress?->address_detail }}" placeholder="Số nhà, Tên đường...">
                            </div>
                            <div class="form-row">
                                <label class="form-label">Địa chỉ 2 (Address Line 2)</label>
                                <input type="text" class="form-control" name="billing[address_line_2]" value="{{ $order->billingAddress?->address_line_2 }}" placeholder="Tòa nhà, Khu phố...">
                            </div>
                            <div class="form-row" style="display:flex; gap:8px;">
                                <div style="flex:1;">
                                    <label class="form-label">Thành phố (City)</label>
                                    <input type="text" class="form-control" name="billing[city_id]" value="{{ $order->billingAddress?->city_id }}">
                                </div>
                                <div style="flex:1;">
                                    <label class="form-label">Tỉnh / Bang (State)</label>
                                    <input type="text" class="form-control" name="billing[state_id]" value="{{ $order->billingAddress?->state_id }}">
                                </div>
                            </div>
                            <div class="form-row" style="display:flex; gap:8px;">
                                <div style="flex:1;">
                                    <label class="form-label">Quốc gia (Country)</label>
                                    <input type="text" class="form-control" name="billing[country_code]" value="{{ $order->billingAddress?->country_code ?? 'VN' }}">
                                </div>
                                <div style="flex:1;">
                                    <label class="form-label">Mã bưu điện (Zip)</label>
                                    <input type="text" class="form-control" name="billing[postal_code]" value="{{ $order->billingAddress?->postal_code }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Internal Note -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ghi chú nội bộ</h3>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" name="internal_note" placeholder="Ghi chú dành cho nhân viên (khách hàng không thấy)">{{ $internalNote }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="show-sidebar">
                
                <!-- Status -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Trạng thái</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <label class="form-label">Trạng thái đơn hàng</label>
                            <select name="status" class="form-control">
                                @foreach(['pending', 'new', 'processing', 'delivering', 'completed', 'cancelled', 'refunded'] as $s)
                                    <option value="{{ $s }}" @selected($statusValue === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Advanced Adjustments -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Phụ phí & Khuyến mãi</h3>
                    </div>
                    <div class="card-body">
                        @if($checkoutSettings->enable_shipping)
                        <div class="form-row">
                            <label class="form-label">Phương thức vận chuyển</label>
                            <select name="shipping_method_id" class="form-control">
                                <option value="">--- Chọn phương thức ---</option>
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
                            <label class="form-label">Mã giảm giá (Coupon Code)</label>
                            <input type="text" name="coupon_code" class="form-control" value="{{ $order->coupons->first()?->coupon_code }}" placeholder="Nhập mã...">
                        </div>
                        @endif

                        @if($loyaltySettings->enabled)
                        <div class="form-row">
                            <label class="form-label">Điểm Loyalty cần đổi</label>
                            @php
                                $redeemedPoints = (int) ($order->metas->where('key', 'redeemed_points')->first()?->value ?? 0);
                            @endphp
                            <input type="number" name="redeemed_points" class="form-control" value="{{ $redeemedPoints }}" min="0" step="1">
                        </div>
                        @endif
                        
                        @if($checkoutSettings->enable_tax)
                        <div class="form-row">
                            <label class="form-label">Ghi đè Thuế (Manual Tax Override)</label>
                            @php
                                $manualTaxAmount = $order->metas->where('key', 'manual_tax_amount')->first()?->value;
                            @endphp
                            <input type="number" name="manual_tax_amount" class="form-control" value="{{ $manualTaxAmount }}" min="0" step="1" placeholder="Bỏ trống để tự tính...">
                            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Hệ thống đang tính: {{ number_format($order->tax_amount ?? 0, 0, ',', '.') }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Order Calculation -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tính toán chi phí</h3>
                    </div>
                    <div class="card-body">
                        <div class="summary-row">
                            <span class="summary-label">Tạm tính (Subtotal)</span>
                            <span class="summary-value" id="js-subtotal-display">{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        
                        @if($checkoutSettings->enable_shipping)
                        <div class="summary-row">
                            <span class="summary-label">Phí vận chuyển</span>
                            <span class="summary-value">{{ number_format($order->shipping?->amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        
                        @if($checkoutSettings->enable_tax)
                        <div class="summary-row">
                            <span class="summary-label">Thuế (Tax)</span>
                            <span class="summary-value">{{ number_format($order->tax_amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        
                        @if($couponSettings->enable_coupons)
                        <div class="summary-row">
                            <span class="summary-label">Giảm giá (Coupons)</span>
                            <span class="summary-value" style="color: #16a34a;">
                                -{{ number_format($discountTotal, 0, ',', '.') }}
                            </span>
                        </div>
                        @endif
                        
                        @if($loyaltySettings->enabled && $loyaltyDiscountTotal > 0)
                        <div class="summary-row">
                            <span class="summary-label">Đổi điểm (Loyalty)</span>
                            <span class="summary-value" style="color: #16a34a;">
                                -{{ number_format($loyaltyDiscountTotal, 0, ',', '.') }}
                            </span>
                        </div>
                        @endif
                        
                        <div class="summary-row" style="border-top: 2px solid #e2e8f0; margin-top: 8px; padding-top: 16px; flex-direction: column; align-items: flex-end;">
                            <div style="display: flex; justify-content: space-between; width: 100%;">
                                <span class="summary-label" style="font-size: 16px; color:#0f172a;">TỔNG CỘNG ĐANG LƯU</span>
                                <span class="summary-value" style="font-size: 20px; color:#2563eb;">{{ number_format($order->total, 0, ',', '.') }}</span>
                            </div>
                            <span style="font-size: 12px; color: #64748b; margin-top: 4px; font-style: italic;">
                                Tổng tiền sẽ được hệ thống tính toán lại tự động dựa trên cấu hình Settings và các giá trị Phụ phí khi bạn nhấn "Lưu thay đổi".
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Refund Modal -->
        <div id="refund-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
            <div style="background: #fff; border-radius: 12px; width: 400px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 18px;">Hoàn tiền Đơn hàng</h3>
                
                <div class="form-row">
                    <label class="form-label">Loại Hoàn tiền</label>
                    <select name="refund_type" id="refund_type" class="form-control" onchange="document.getElementById('refund_amount_container').style.display = this.value === 'partial' ? 'block' : 'none'">
                        <option value="full">Hoàn tiền Toàn phần</option>
                        <option value="partial">Hoàn tiền Một phần</option>
                    </select>
                </div>
                
                <div class="form-row" id="refund_amount_container" style="display: none;">
                    <label class="form-label">Số tiền hoàn (Nếu là một phần)</label>
                    <input type="number" name="refund_amount" id="refund_amount" class="form-control" min="0" step="1" placeholder="Nhập số tiền...">
                </div>
                
                <div class="form-row">
                    <label class="form-label">Lý do hoàn tiền</label>
                    <textarea name="refund_reason" id="refund_reason" class="form-control" placeholder="Nhập lý do..."></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeRefundModal()" style="padding: 8px 16px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer;">Đóng</button>
                    <button type="button" class="btn btn-primary" onclick="confirmRefundModal()" style="padding: 8px 16px; background: #3b82f6; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Xác nhận</button>
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
                
                // Calculate item totals
                document.querySelectorAll('.order-item-row').forEach(row => {
                    const price = parseFloat(row.querySelector('.js-item-price').value) || 0;
                    const qty = parseInt(row.querySelector('.js-item-qty').value) || 0;
                    const itemTotal = price * qty;
                    
                    row.querySelector('.js-item-total-display').innerText = formatMoney(itemTotal);
                    subtotal += itemTotal;
                });

                document.getElementById('js-subtotal-display').innerText = formatMoney(subtotal);
            };

            // Attach event listeners
            document.querySelectorAll('.js-item-price, .js-item-qty').forEach(el => {
                el.addEventListener('input', calculateSubtotals);
            });

            // Refund Modal Logic
            const statusSelect = document.querySelector('select[name="status"]');
            const refundModal = document.getElementById('refund-modal');
            let originalStatus = statusSelect.value;
            
            statusSelect.addEventListener('change', function(e) {
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
