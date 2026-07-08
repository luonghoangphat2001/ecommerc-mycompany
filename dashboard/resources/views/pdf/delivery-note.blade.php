<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Note #{{ $order->number }}</title>
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
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #9ca3af; }
        .signature-box { margin-top: 60px; display: flex; justify-content: space-between; }
        .signature { width: 200px; border-top: 1px solid #333; text-align: center; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div class="logo">E-COMMERCE SYSTEM</div>
            <div style="text-align: right;">
                <h1 style="margin: 0;">DELIVERY NOTE</h1>
                <p>Order #{{ $order->number }}</p>
                <p>Date: {{ date('M d, Y') }}</p>
            </div>
        </div>

        <div class="info">
            <table>
                <tr>
                    <td>
                        <strong>Recipient:</strong><br>
                        {{ $order->customer_name }}<br>
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
                    <th>Weight/Size</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->productItems as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? $item->name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td> - </td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature-box">
            <div class="signature">Warehouse Officer</div>
            <div class="signature" style="float: right;">Customer Signature</div>
        </div>

        <div style="clear: both;"></div>

        <div class="footer">
            Generated on {{ date('Y-m-d H:i:s') }}
        </div>
    </div>
</body>
</html>
