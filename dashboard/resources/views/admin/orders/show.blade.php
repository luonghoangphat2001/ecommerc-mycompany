@extends('admin.layouts.app', ['title' => 'Order #' . $order->id])

@section('content')
    @php($statusValue = $order->status instanceof \BackedEnum ? $order->status->value : (string) $order->status)

    <div class="page-header">
        <div>
            <h1 class="page-title">Order #{{ $order->id }}</h1>
            <p class="page-description">Order Number: {{ $order->number }} · {{ $order->currency }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('admin.orders.index') }}">Quay lại</a>
            <a class="btn" href="{{ route('admin.orders.edit', $order->id) }}">Sửa</a>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="stats">
        <div class="stat">
            <div class="label">Status</div>
            <div class="value" style="font-size:24px;">{{ $statusValue }}</div>
        </div>
        <div class="stat">
            <div class="label">Total</div>
            <div class="value" style="font-size:24px;">{{ $order->total }}</div>
        </div>
        <div class="stat">
            <div class="label">Payments</div>
            <div class="value" style="font-size:24px;">{{ $order->payments->count() }}</div>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">Cập nhật trạng thái</h2>
        <form method="post" action="{{ route('admin.orders.status', $order->id) }}" class="inline-form">
            @csrf
            <div>
                <label for="status">Trạng thái</label>
                <select id="status" name="status">
                    @foreach (['pending', 'new', 'processing', 'delivering', 'completed', 'cancelled', 'refunded'] as $status)
                        <option value="{{ $status }}" @selected($statusValue === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn" type="submit">Cập nhật</button>
        </form>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 class="section-title" style="margin-top:0;">Items</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->items as $item)
                        <tr>
                            <td>{{ $item->name ?? ('Item #' . $item->id) }}</td>
                            <td>{{ $item->qty ?? 0 }}</td>
                            <td>{{ $item->total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">Không có dữ liệu</td></tr>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->payments as $payment)
                        <tr>
                            <td>{{ $payment->method }}</td>
                            <td>{{ $payment->amount }} {{ $payment->currency }}</td>
                            <td>{{ $payment->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">Không có dữ liệu</td></tr>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->refunds as $refund)
                        <tr>
                            <td>{{ $refund->amount }}</td>
                            <td>{{ $refund->reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">Không có dữ liệu</td></tr>
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
@endsection
