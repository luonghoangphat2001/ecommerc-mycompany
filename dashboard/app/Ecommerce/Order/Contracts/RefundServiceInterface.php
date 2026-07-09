<?php

namespace App\Ecommerce\Order\Contracts;

use App\Models\Order;
use App\Models\OrderRefund;

interface RefundServiceInterface
{
    /**
     * Process a refund for an order.
     *
     * @param Order $order The order to refund.
     * @param float $amount The amount to refund.
     * @param string $reason The reason for the refund.
     * @param string $type The type of refund ('full' or 'partial').
     * @return OrderRefund|null The created refund record, or null if failed.
     * @throws \Exception If the refund cannot be processed.
     */
    public function processRefund(Order $order, float $amount, string $reason, string $type): ?OrderRefund;

    /**
     * Validate if an order can be refunded for the specified amount.
     *
     * @param Order $order
     * @param float $amount
     * @return bool
     * @throws \Exception
     */
    public function validateRefundAvailability(Order $order, float $amount): bool;
}
