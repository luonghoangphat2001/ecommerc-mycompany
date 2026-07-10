<?php

namespace App\Ecommerce\Cart\Contracts;

interface CartCalculationServiceInterface
{
    /**
     * Calculate cart totals
     *
     * @param array $items
     * @param string|null $country
     * @param string|null $shippingMethodId
     * @param string|null $couponCode
     * @return array
     */
    public function calculate(array $items, ?string $country = 'VN', ?string $state = null, ?string $shippingMethodId = null, ?string $couponCode = null): array;

    /**
     * Get available shipping methods
     *
     * @param int $subtotal
     * @param string|null $country
     * @param string|null $state
     * @return array
     */
    public function getAvailableShippingMethods(int $subtotal, ?string $country = 'VN', ?string $state = null): array;
}
