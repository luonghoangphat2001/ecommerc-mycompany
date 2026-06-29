<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\OrderRefund;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Order::class;
    }

    protected function title(): string
    {
        return 'Orders';
    }

    protected function routePrefix(): string
    {
        return 'admin.orders';
    }

    protected function searchable(): array
    {
        return ['number', 'status', 'currency'];
    }

    protected function fields(): array
    {
        return [
            'status' => ['label' => 'Status', 'rules' => ['required', 'string', 'max:30']],
            'currency' => ['label' => 'Currency', 'rules' => ['required', 'string', 'max:10']],
            'total' => ['label' => 'Tổng tiền', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
        ];
    }

    public function show(int $id): View
    {
        $order = Order::with(['items', 'payments', 'refunds'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,new,processing,delivering,completed,cancelled,refunded'],
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $data['status']]);

        return redirect()->route('admin.orders.show', $order->id)->with('status', 'Đã cập nhật trạng thái đơn hàng');
    }

    public function storePayment(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'method' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', 'max:50'],
            'provider' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $order = Order::findOrFail($id);

        Payment::create($data + [
            'order_id' => $order->id,
            'currency' => $order->currency ?: 'VND',
        ]);

        return redirect()->route('admin.orders.show', $order->id)->with('status', 'Đã thêm payment');
    }

    public function storeRefund(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string'],
        ]);

        $order = Order::findOrFail($id);
        OrderRefund::create($data + ['order_id' => $order->id]);

        return redirect()->route('admin.orders.show', $order->id)->with('status', 'Đã tạo refund');
    }
}
