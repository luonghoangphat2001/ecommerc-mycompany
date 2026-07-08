<?php

namespace App\Ecommerce\Shipping\Services\Drivers;

use App\Ecommerce\Shipping\Contracts\ShippingProviderInterface;
use App\Models\Order;

class FreeShippingDriver implements ShippingProviderInterface
{
    /**
     * @inheritDoc
     */
    public function calculateFee(int $subtotal, array $destination, array $options = []): int
    {
        $minAmount = (int) ($options['min_amount'] ?? 0);

        return ($subtotal >= $minAmount) ? 0 : 0; // Or handle as "not available" if negative
    }

    /**
     * @inheritDoc
     */
    public function createOrder(Order $order): string
    {
        return 'FREE-' . $order->number . '-' . uniqid();
    }

    /**
     * @inheritDoc
     */
    public function cancelOrder(string $trackingNumber): bool
    {
        return true;
    }
}
