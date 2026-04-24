<!DOCTYPE html>
<html>

<head>
    <title>Xác nhận đơn hàng</title>
</head>

<body>
    <h2>Xin chào {{ $order['customer_name'] }},</h2>
    <p>Cảm ơn bạn đã đặt hàng! Đây là thông tin đơn hàng của bạn:</p>
    <ul>
        <li>Mã đơn hàng: {{ $order['order_id'] }}</li>
        <li>Tổng tiền: {{ number_format($order['total_price'], 0, ',', '.') }} VNĐ</li>
        <li>Ngày đặt: {{ $order['date'] }}</li>
    </ul>
    <p>Chúng tôi sẽ liên hệ bạn sớm nhất!</p>
</body>

</html>