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
                                @foreach ($order->items as $index => $item)
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
                            <h3 class="card-title">Địa chỉ nhận hàng</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <label class="form-label">Họ Tên</label>
                                <div style="display:flex; gap:8px;">
                                    <input type="text" class="form-control" name="shipping[first_name]" value="{{ $order->shippingAddress?->first_name }}" placeholder="First Name">
                                    <input type="text" class="form-control" name="shipping[last_name]" value="{{ $order->shippingAddress?->last_name }}" placeholder="Last Name">
                                </div>
                            </div>
                            <div class="form-row">
                                <label class="form-label">SĐT</label>
                                <input type="text" class="form-control" name="shipping[phone]" value="{{ $order->shippingAddress?->phone }}">
                            </div>
                            <div class="form-row">
                                <label class="form-label">Địa chỉ chi tiết</label>
                                <input type="text" class="form-control" name="shipping[address_detail]" value="{{ $order->shippingAddress?->address_detail }}">
                            </div>
                        </div>
                    </div>

                    <!-- Billing -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Địa chỉ thanh toán</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <label class="form-label">Họ Tên</label>
                                <div style="display:flex; gap:8px;">
                                    <input type="text" class="form-control" name="billing[first_name]" value="{{ $order->billingAddress?->first_name }}" placeholder="First Name">
                                    <input type="text" class="form-control" name="billing[last_name]" value="{{ $order->billingAddress?->last_name }}" placeholder="Last Name">
                                </div>
                            </div>
                            <div class="form-row">
                                <label class="form-label">SĐT</label>
                                <input type="text" class="form-control" name="billing[phone]" value="{{ $order->billingAddress?->phone }}">
                            </div>
                            <div class="form-row">
                                <label class="form-label">Địa chỉ chi tiết</label>
                                <input type="text" class="form-control" name="billing[address_detail]" value="{{ $order->billingAddress?->address_detail }}">
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
                                Tổng tiền sẽ được hệ thống tính toán lại tự động dựa trên cấu hình Settings khi bạn nhấn "Lưu thay đổi".
                            </span>
                        </div>
                    </div>
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
        });
    </script>
@endsection
