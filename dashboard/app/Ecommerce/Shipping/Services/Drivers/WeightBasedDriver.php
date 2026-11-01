<?php

namespace App\Ecommerce\Shipping\Services\Drivers;

use App\Ecommerce\Shipping\Contracts\ShippingProviderInterface;
use App\Models\Order;

class WeightBasedDriver implements ShippingProviderInterface
{
    /**
     * @inheritDoc
     */
    public function calculateFee(int $subtotal, array $destination, array $options = []): int
    {
        // 1. Free shipping threshold
        $threshold = (int) ($options['free_shipping_threshold'] ?? 0);
        if ($threshold > 0 && $subtotal >= $threshold) {
            return 0;
        }

        $weight = max(1, (int) ($options['weight'] ?? 500)); // in grams
        $rules = $options['rules'] ?? [];
        $defaultCost = (int) ($options['default_cost'] ?? $options['cost'] ?? 30000);
        $additionalPerKg = (int) ($options['additional_cost_per_kg'] ?? 0);

        if (empty($rules) || !is_array($rules)) {
            return $defaultCost;
        }

        // Sort rules by min_weight
        usort($rules, fn($a, $b) => ($a['min_weight'] ?? 0) <=> ($b['min_weight'] ?? 0));

        $highestTier = null;
        foreach ($rules as $rule) {
            $min = (int) ($rule['min_weight'] ?? 0);
            $max = (int) ($rule['max_weight'] ?? PHP_INT_MAX);
            $cost = (int) ($rule['cost'] ?? $defaultCost);

            $highestTier = $rule;

            if ($weight >= $min && $weight <= $max) {
                return max(0, $cost);
            }
        }

        // If weight exceeds all defined tiers, apply incremental fee per extra kg
        if ($highestTier && $weight > ($highestTier['max_weight'] ?? 0)) {
            $baseCost = (int) ($highestTier['cost'] ?? $defaultCost);
            $extraWeight = $weight - (int) $highestTier['max_weight'];
            $extraKg = (int) ceil($extraWeight / 1000);
            $ratePerKg = $additionalPerKg > 0 ? $additionalPerKg : 5000;

            return max(0, $baseCost + ($extraKg * $ratePerKg));
        }

        return max(0, $defaultCost);
    }

    /**
     * @inheritDoc
     */
    public function createOrder(Order $order): string
    {
        return 'WEIGHT-' . $order->number . '-' . uniqid();
    }

    /**
     * @inheritDoc
     */
    public function cancelOrder(string $trackingNumber): bool
    {
        return true;
    }
}
