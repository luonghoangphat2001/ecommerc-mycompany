<?php

namespace App\Ecommerce\Payment\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentServiceInterface
{
    /**
     * Process an arbitrary payment against an order.
     *
     * @param  string  $method  (e.g. 'stripe', 'vnpay', 'cod')
     * @param  float|int  $amount
     */
    public function process(Order $order, string $method, $amount): Payment;

    /** Record an admin payment while serializing writes for the order. */
    public function record(Order $order, array $data): Payment;
}
