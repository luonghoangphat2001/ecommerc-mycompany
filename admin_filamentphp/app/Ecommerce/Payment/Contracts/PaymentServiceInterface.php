<?php

namespace App\Ecommerce\Payment\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentServiceInterface
{
    /**
     * Process an arbitrary payment against an order.
     * 
     * @param Order $order
     * @param string $method (e.g. 'stripe', 'vnpay', 'cod')
     * @param float|int $amount
     * @return Payment
     */
    public function process(Order $order, string $method, $amount): Payment;
}
