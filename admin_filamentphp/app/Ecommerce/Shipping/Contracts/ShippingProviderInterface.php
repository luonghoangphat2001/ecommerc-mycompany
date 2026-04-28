<?php

namespace App\Ecommerce\Shipping\Contracts;

use App\Models\OrderItem;
use App\Models\Order;

interface ShippingProviderInterface
{
    /**
     * Calculate shipping fee for the given parameters.
     *
     * @param int $subtotal (in cents)
     * @param array $destination ['country', 'state', 'postcode', 'ward']
     * @param array $options
     * @return int Fee in cents
     */
    public function calculateFee(int $subtotal, array $destination, array $options = []): int;

    /**
     * Create a shipping order/package in the provider's system.
     *
     * @param Order $order
     * @return string Tracking number or provider reference
     */
    public function createOrder(Order $order): string;

    /**
     * Cancel a shipping order.
     *
     * @param string $trackingNumber
     * @return bool
     */
    public function cancelOrder(string $trackingNumber): bool;
}
