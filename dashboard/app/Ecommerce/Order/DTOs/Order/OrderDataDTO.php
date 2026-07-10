<?php

namespace App\Ecommerce\Order\DTOs\Order;

class OrderDataDTO
{
    public function __construct(
        public ?int $customerId = null,
        public string $email,
        public string $phone,
        public array $shippingAddress,
        public array $billingAddress,
        public array $items, // [['product_id' => 1, 'qty' => 2, 'price' => 100], ...]
        public ?string $shippingMethod = null,
        public ?string $paymentMethod = null,
        public ?string $couponCode = null,
        public string $currency = 'VND',
        public ?string $notes = null,
        public float $shippingFee = 0,
        public float $taxAmount = 0,
        public float $discountAmount = 0,
        public float $grandTotal = 0,
    ) {}

    /**
     * Create DTO from request data.
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            customerId: $data['customer_id'] ?? null,
            email: $data['email'],
            phone: $data['phone'],
            shippingAddress: $data['shipping_address'] ?? [],
            billingAddress: $data['billing_address'] ?? $data['shipping_address'] ?? [],
            items: $data['items'] ?? [],
            shippingMethod: $data['shipping_method'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            couponCode: $data['coupon_code'] ?? null,
            currency: $data['currency'] ?? 'VND',
            notes: $data['notes'] ?? null,
            shippingFee: (float) ($data['shipping_fee'] ?? 0),
            taxAmount: (float) ($data['tax_amount'] ?? 0),
            discountAmount: (float) ($data['discount_amount'] ?? 0),
            grandTotal: (float) ($data['grand_total'] ?? 0),
        );
    }
}
