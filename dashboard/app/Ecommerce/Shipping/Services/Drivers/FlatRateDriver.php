<?php

namespace App\Ecommerce\Shipping\Services\Drivers;

use App\Ecommerce\Shipping\Contracts\ShippingProviderInterface;
use App\Models\Order;

class FlatRateDriver implements ShippingProviderInterface
{
    /**
     * @inheritDoc
     */
    public function calculateFee(int $subtotal, array $destination, array $options = []): int
    {
        $freeShippingThreshold = (int) ($options['free_shipping_threshold'] ?? 0);
        if ($freeShippingThreshold > 0 && $subtotal >= $freeShippingThreshold) {
            return 0;
        }

        return max(0, (int) ($options['cost'] ?? 0));
    }

    /**
     * @inheritDoc
     */
    public function createOrder(Order $order): string
    {
        return 'LOCAL-' . $order->number . '-' . uniqid();
    }

    /**
     * @inheritDoc
     */
    public function cancelOrder(string $trackingNumber): bool
    {
        return true;
    }
}
