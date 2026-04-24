<x-mail::message>
# Thông báo đơn hàng mới #{{ $order->number }}

Chào Admin,

Hệ thống vừa nhận được một đơn hàng mới. Dưới đây là thông báo chi tiết:

- **Loại đơn:** {{ ucfirst($order->type) }}
- **Khách hàng:** {{ $order->customer->name ?? $order->email ?? 'Khách vãng lai' }}
- **Email:** {{ $order->email ?? optional($order->customer)->email ?? 'N/A' }}
- **Số điện thoại:** {{ $order->phone ?? optional($order->customer)->phone ?? 'N/A' }}
- **Tổng số tiền:** **{{ number_format($order->total_price) }} VND**

<x-mail::table>
| Sản phẩm | Số lượng | Thành tiền |
| :--- | :---: | :--- |
@foreach ($order->items as $item)
| {{ optional($item->product)->name ?? 'Sản phẩm đã xóa' }} | {{ $item->qty }} | {{ number_format($item->qty * $item->unit_price) }} |
@endforeach
</x-mail::table>

**Ghi chú từ khách:**
{{ $order->notes ?: 'Không có' }}

<x-mail::button :url="config('app.url') . '/admin/shop/orders/' . $order->id . '/edit'">
Xử lý đơn hàng ngay
</x-mail::button>

Trân trọng,<br>
Hệ thống {{ config('app.name') }}
</x-mail::message>
