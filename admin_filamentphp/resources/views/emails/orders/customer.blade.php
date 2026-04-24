<x-mail::message>
# Cảm ơn bạn đã đặt hàng!

Chào {{ $order->customer->name }},

Chúng tôi đã nhận được đơn hàng **#{{ $order->number }}** của bạn. Dưới đây là chi tiết đơn hàng:

<x-mail::table>
| Sản phẩm | Số lượng | Đơn giá | Thành tiền |
| :--- | :---: | :---: | :--- |
@foreach ($order->items as $item)
| {{ $item->product->name }} | {{ $item->qty }} | {{ number_format($item->unit_price) }} | {{ number_format($item->qty * $item->unit_price) }} |
@endforeach
| **Tổng cộng** | | | **{{ number_format($order->total_price) }} VND** |
</x-mail::table>

**Thông tin giao hàng:**
- **Người nhận:** {{ $order->customer->name }}
- **Số điện thoại:** {{ $order->customer->phone }}
- **Địa chỉ:** {{ $order->address->street }}, {{ $order->address->city }}

Chúng tôi sẽ sớm liên hệ với bạn để xác nhận và giao hàng.

<x-mail::button :url="config('app.url')">
Xem trạng thái đơn hàng
</x-mail::button>

Trân trọng,<br>
{{ config('app.name') }}
</x-mail::message>
