<?php

namespace App\Ecommerce\Payment\Services;

use App\Ecommerce\Payment\Contracts\PaymentRepositoryInterface;
use App\Ecommerce\Payment\Contracts\PaymentServiceInterface;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService implements PaymentServiceInterface
{
    protected $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Process an arbitrary payment against an order.
     *
     * @param  string  $method  (e.g. 'stripe', 'vnpay', 'cod')
     * @param  float|int  $amount
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

    public function record(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data): Payment {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $reference = $data['reference'] ?? null;

            if ($reference !== null && $reference !== '') {
                $existing = $lockedOrder->payments()->where('reference', $reference)->first();
                if ($existing) {
                    return $existing;
                }
            }

            return $this->paymentRepository->create([
                ...$data,
                'order_id' => $lockedOrder->id,
                'currency' => $lockedOrder->currency ?: 'VND',
            ]);
        }, 3);
    }
}
