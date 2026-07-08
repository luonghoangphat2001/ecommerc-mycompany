<?php

namespace App\Ecommerce\Order\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use App\Models\Order;
use App\Models\OrderRefund;

interface OrderRefundRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get total amount refunded for an order.
     *
     * @param Order $order
     * @return int
     */
    public function getTotalRefunded(Order $order): int;
}
