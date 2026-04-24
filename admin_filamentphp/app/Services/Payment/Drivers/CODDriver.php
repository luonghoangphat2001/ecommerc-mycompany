<?php

namespace App\Services\Payment\Drivers;

use App\Contracts\Shop\PaymentProviderInterface;
use App\Models\Order;

class CODDriver implements PaymentProviderInterface
{
    /**
     * @inheritDoc
     */
    public function authorize(Order $order, array $payload = []): array
    {
        return [
            'success' => true,
            'transaction_id' => 'COD-' . $order->number . '-' . time(),
            'message' => 'Cash on delivery authorized.',
        ];
    }

    /**
     * @inheritDoc
     */
    public function capture(string $transactionId, ?int $amount = null): bool
    {
        // COD capture usually happens manually upon delivery
        return true;
    }

    /**
     * @inheritDoc
     */
    public function refund(string $transactionId, ?int $amount = null): bool
    {
        // Refund for COD is usually manual
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentUrl(Order $order): ?string
    {
        return null; // No redirection for COD
    }
}
