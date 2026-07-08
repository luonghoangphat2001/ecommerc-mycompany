<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Đơn hàng mới - #{{ $order->number ?? '0001' }}</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.6; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; }
        .footer { font-size: 12px; color: #777; margin-top: 20px; text-align: center; }
        .order-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .order-table th, .order-table td { border-bottom: 1px solid #eee; padding: 10px; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Cảm ơn bạn đã đặt hàng!</h2>
            <p>Mã đơn hàng: <strong>#{{ $order->number ?? '0001' }}</strong></p>
        </div>
        
        <p>Xin chào {{ $order->customer_name ?? 'Khách' }},</p>
        <p>Chúng tôi đã nhận được đơn đặt hàng của bạn và đang tiến hành xử lý.</p>

        <table class="order-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>SL</th>
                    <th>Giá</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sản phẩm mẫu</td>
                    <td>1</td>
                    <td>100,000đ</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">Tạm tính:</th>
                    <td>100,000đ</td>
                </tr>
                <tr>
                    <th colspan="2">Thuế:</th>
                    <td>10,000đ</td>
                </tr>
                <tr>
                    <th colspan="2">Tổng cộng:</th>
                    <td><strong>110,000đ</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Mọi quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>
