<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class PaymentService
{
    protected $paymentRepository;

    public function __construct(\App\Contracts\Repositories\PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Process an arbitrary payment against an order.
     * 
     * @param Order $order
     * @param string $method (e.g. 'stripe', 'vnpay', 'cod')
     * @param float|int $amount
     * @return Payment
     */
    public function process(Order $order, string $method, $amount): \App\Models\Payment
    {
        return $this->paymentRepository->create([
            'order_id' => $order->id,
            'amount' => $amount,
            'currency' => $order->currency,
            'method' => $method,
            'provider' => 'system', // Strategy pattern can be injected later here
            'status' => 'pending',
        ]);
    }
}
