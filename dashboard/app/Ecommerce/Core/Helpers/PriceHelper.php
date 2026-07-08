<?php

namespace App\Ecommerce\Core\Helpers;

class PriceHelper
{
    /**
     * Round a price in cents using E-commerce rules.
     * Usually rounds to the nearest integer.
     *
     * @param float $amount
     * @return int
     */
    public static function round(float $amount): int
    {
        return (int) round($amount, 0, PHP_ROUND_HALF_UP);
    }

    /**
     * Format cents to a human-readable string.
     *
     * @param int $cents
     * @param string $currency
     * @return string
     */
    public static function format(int $cents, string $currency = 'VND'): string
    {
        if ($currency === 'VND') {
            return number_format($cents, 0, ',', '.') . ' ₫';
        }

        return $currency . ' ' . number_format($cents / 100, 2);
    }
}
