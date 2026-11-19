<?php

namespace App\Ecommerce\Order\Concerns;

use App\Models\Order;

trait LocksOrderForUpdate
{
    protected function lockOrderForUpdate(Order | int $order): Order
    {
        $orderId = $order instanceof Order ? $order->getKey() : $order;

        return Order::query()->lockForUpdate()->findOrFail($orderId);
    }
}
