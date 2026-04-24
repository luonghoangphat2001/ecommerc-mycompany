<?php

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Traits\HandleTransactions;

class UpdateOrderStatusAction
{
    use HandleTransactions;

    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Handle updating order status.
     *
     * @param Order $order
     * @param string|OrderStatus $newStatus
     * @return bool
     */
    public function execute(Order $order, $newStatus)
    {
        return $this->useTransaction(function () use ($order, $newStatus) {
            $updated = $this->orderRepository->update($order->id, [
                'status' => $newStatus
            ]);

            if ($updated) {
                // Future point to dispatch Specific Status Change events
                // e.g. event(new OrderShipped($order));
            }

            return $updated;
        });
    }
}
