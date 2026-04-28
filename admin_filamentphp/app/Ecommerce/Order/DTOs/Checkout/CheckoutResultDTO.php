<?php

namespace App\Ecommerce\Order\DTOs\Checkout;

class CheckoutResultDTO
{
    public function __construct(
        public int $subtotal = 0,
        public int $taxTotal = 0,
        public int $shippingTotal = 0,
        public int $discountTotal = 0,
        public int $total = 0,
        public array $appliedTaxes = [],
        public array $appliedFees = [],
        public string $currency = 'VND',
        public float $exchangeRate = 1.0,
    ) {}

    /**
     * Get summary of the calculation.
     */
    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'tax_total' => $this->taxTotal,
            'shipping_total' => $this->shippingTotal,
            'discount_total' => $this->discountTotal,
            'total' => $this->total,
            'currency' => $this->currency,
            'exchange_rate' => $this->exchangeRate,
        ];
    }
}
