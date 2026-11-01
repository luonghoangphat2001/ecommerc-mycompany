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

        if ($minAmount > 0 && $subtotal < $minAmount) {
            return -1; // -1 represents not eligible/unavailable
        }

        return 0;
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
