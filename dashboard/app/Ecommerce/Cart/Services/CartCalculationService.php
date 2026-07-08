<?php

namespace App\Ecommerce\Cart\Services;

use App\Ecommerce\Cart\Contracts\CartCalculationServiceInterface;
use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Settings\CheckoutSettings;

class CartCalculationService implements CartCalculationServiceInterface
{
    public function __construct(
        protected TaxServiceInterface $taxService,
        protected ShippingServiceInterface $shippingService,
        protected CheckoutSettings $checkoutSettings
    ) {}

    /**
     * Calculate cart totals using services
     *
     * @param array $items Validated cart items
     * @param string|null $country Shipping country
     * @param string|null $state Shipping state
     * @return array
     */
    public function calculate(array $items, ?string $country = 'VN', ?string $state = null): array
    {
        $subtotal = $this->calculateSubtotal($items);
        
        // Get shipping using ShippingService
        $shipping = $this->calculateShipping($subtotal, $country, $state);
        
        // Get tax using TaxService
        $tax = $this->calculateTax($subtotal, $country);
        
        $total = $subtotal + $shipping['amount'] + $tax['amount'];
        
        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
            'items_count' => collect($items)->sum('quantity'),
        ];
    }

    /**
     * Calculate subtotal from items
     */
    private function calculateSubtotal(array $items): int
    {
        return collect($items)->sum(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 0));
    }

    /**
     * Calculate shipping using ShippingService
     */
    private function calculateShipping(int $subtotal, ?string $country, ?string $state): array
    {
        $methods = $this->shippingService->getAvailableMethods($country, $state, null, null, $subtotal);
        
        $defaultMethod = $methods->first();
        
        if (!$defaultMethod) {
            return [
                'amount' => $subtotal >= 500000 ? 0 : 30000,
                'method_name' => 'Mặc định',
                'method_id' => null,
            ];
        }
        
        return [
            'amount' => $defaultMethod['cost'] ?? 0,
            'method_name' => $defaultMethod['name'] ?? 'Giao hàng',
            'method_id' => $defaultMethod['method_id'] ?? null,
        ];
    }

    /**
     * Calculate tax using TaxService
     */
    private function calculateTax(int $subtotal, ?string $country): array
    {
        // Get default tax class
        $defaultTaxClass = \App\Models\TaxClass::first();
        
        if (!$defaultTaxClass) {
            return [
                'amount' => (int) round($subtotal * 0.1), // Fallback 10%
                'rate_name' => 'VAT 10%',
                'rate_percent' => 10,
            ];
        }
        
        $taxResult = $this->taxService->calculate($defaultTaxClass, $subtotal, $country);
        
        return [
            'amount' => $taxResult['amount'] ?? 0,
            'rate_name' => $taxResult['rate_name'] ?? 'VAT',
            'rate_percent' => $taxResult['rate_percent'] ?? 0,
            'tax_rate_id' => $taxResult['tax_rate_id'] ?? null,
        ];
    }

    /**
     * Get available shipping methods for cart
     */
    public function getAvailableShippingMethods(int $subtotal, ?string $country = 'VN', ?string $state = null): array
    {
        return $this->shippingService->getAvailableMethods($country, $state, null, null, $subtotal)->toArray();
    }
}
