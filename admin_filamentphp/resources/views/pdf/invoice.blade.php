<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->number }}</title>
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 14px; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .logo { font-size: 24px; font-weight: bold; color: #4F46E5; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; }
        .table th { background: #f9fafb; font-weight: bold; text-transform: uppercase; font-size: 12px; }
        .totals { margin-top: 30px; float: right; width: 300px; }
        .totals table { width: 100%; }
        .totals td { padding: 5px 0; }
        .totals .bold { font-weight: bold; font-size: 18px; color: #4F46E5; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div class="logo">E-COMMERCE SYSTEM</div>
            <div style="text-align: right;">
                <h1 style="margin: 0;">INVOICE</h1>
                <p>#{{ $order->number }}</p>
                <p>Date: {{ $order->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="info">
            <table>
                <tr>
                    <td>
                        <strong>Bill To:</strong><br>
                        {{ $order->customer_name }}<br>
                        {{ $order->customer_email }}<br>
                        {{ $order->customer_phone }}
                    </td>
                    <td>
                        <strong>Shipping Address:</strong><br>
                        @if($order->shippingAddress)
                            {{ $order->shippingAddress->first_name }} {{ $order->shippingAddress->last_name }}<br>
                            {{ $order->shippingAddress->street }}<br>
                            {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->zip }}<br>
                            {{ $order->shippingAddress->country }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->productItems as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? $item->name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ number_format($item->unit_price) }} {{ $order->currency }}</td>
                        <td>{{ number_format($item->total) }} {{ $order->currency }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td style="text-align: right;">{{ number_format($order->subtotal) }} {{ $order->currency }}</td>
                </tr>
                @foreach($order->taxItems as $item)
                    <tr>
                        <td>Tax ({{ $item->name }}):</td>
                        <td style="text-align: right;">{{ number_format($item->total) }} {{ $order->currency }}</td>
                    </tr>
                @endforeach
                @foreach($order->shippingItems as $item)
                    <tr>
                        <td>Shipping ({{ $item->name }}):</td>
                        <td style="text-align: right;">{{ number_format($item->total) }} {{ $order->currency }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="bold">Total:</td>
                    <td class="bold" style="text-align: right;">{{ number_format($order->total) }} {{ $order->currency }}</td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <div class="footer">
            Thank you for your business!<br>
            If you have any questions, please contact us at support@example.com
        </div>
    </div>
</body>
</html>
