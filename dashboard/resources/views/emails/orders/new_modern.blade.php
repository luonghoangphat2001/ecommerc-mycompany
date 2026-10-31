<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Đơn hàng hiện đại - #{{ $order->number ?? '0001' }}</title>
    <style>
        body { font-family: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #0f172a; background: #f8fafc; margin: 0; padding: 0; }
        .wrapper { background: #f8fafc; padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04); }
        .header { background: #09253e; color: #ffffff; padding: 36px 30px; text-align: center; }
        .content { padding: 30px; }
        .order-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .total-row { display: flex; justify-content: space-between; padding: 10px 0; border-top: 1px solid #e2e8f0; }
        .footer { text-align: center; padding: 30px; font-size: 13px; color: #64748b; }
        .button { display: inline-block; padding: 12px 24px; background: #004687; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1 style="margin:0; font-size: 24px;">Đơn hàng của bạn đã sẵn sàng!</h1>
            </div>
            
            <div class="content">
                <p>Chào {{ $order->customer_name ?? 'Bạn' }},</p>
                <p>Rất vui mừng thông báo rằng đơn hàng <strong>#{{ $order->number ?? '0001' }}</strong> đã được ghi nhận vào hệ thống của chúng tôi.</p>

                <div class="order-card">
                    <h3 style="margin-top:0">Tóm tắt đơn hàng</h3>
                    <div style="margin-bottom: 5px;"><strong>{{ $order->items_count ?? 1 }} mặt hàng</strong></div>
                    <div style="font-size: 14px; color: #718096;">Dự kiến giao hàng trong 2-3 ngày làm việc.</div>
                </div>

                <div style="border-top: 2px solid #edf2f7; padding-top: 20px;">
                    <div style="margin-bottom: 10px;">
                        <span style="color: #718096;">Tạm tính:</span>
                        <span style="float: right;">100,000đ</span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span style="color: #718096;">Thuế:</span>
                        <span style="float: right;">10,000đ</span>
                    </div>
                    <div style="font-size: 18px; font-weight: bold; margin-top: 15px; border-top: 1px solid #edf2f7; padding-top: 15px;">
                        <span>Tổng thanh toán:</span>
                        <span style="float: right; color: #2d3748;">110,000đ</span>
                    </div>
                </div>

                <div style="text-align: center;">
                    <a href="#" class="button">Chi tiết đơn hàng</a>
                </div>
            </div>

            <div class="footer">
                <p>Bạn nhận được email này vì bạn vừa thực hiện đơn hàng trên {{ config('app.name') }}.</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }} Team.</p>
            </div>
        </div>
    </div>
</body>
</html>
