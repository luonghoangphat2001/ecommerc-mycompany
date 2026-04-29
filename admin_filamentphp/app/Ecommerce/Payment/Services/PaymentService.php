<?php

namespace App\Ecommerce\Payment\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Ecommerce\Payment\Contracts\PaymentRepositoryInterface;

class PaymentService
{
    protected $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
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
    public function process(Order $order, string $method, $amount): Payment
    {
        return $this->paymentRepository->create([
            'order_id' => $order->id,
            'amount' => $amount,
            'currency' => $order->currency,
            'method' => $method,
            'provider' => 'system',
            'status' => 'pending',
        ]);
    }
}
