<?php

namespace App\Ecommerce\Order\Repositories;

use App\Ecommerce\Order\Contracts\OrderRefundRepositoryInterface;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Ecommerce\Core\Repositories\BaseRepository;

class EloquentOrderRefundRepository extends BaseRepository implements OrderRefundRepositoryInterface
{
    public function __construct(OrderRefund $model)
    {
        parent::__construct($model);
    }

    /**
     * Get total amount refunded for an order.
     *
     * @param Order $order
     * @return int
     */
    public function getTotalRefunded(Order $order): int
    {
        return (int) $this->model->newQuery()
            ->where('order_id', $order->id)
            ->sum('amount');
    }
}
