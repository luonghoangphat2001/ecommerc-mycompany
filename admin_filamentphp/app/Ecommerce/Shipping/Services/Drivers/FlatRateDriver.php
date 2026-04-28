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
        // For Flat Rate, we usually take the cost from the options/method settings
        return (int) ($options['cost'] ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function createOrder(Order $order): string
    {
        // Flat rate is usually self-fulfilled, return a local reference
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
