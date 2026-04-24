<?php

namespace App\DTOs\Order;

class OrderDataDTO
{
    public function __construct(
        public ?int $customerId = null,
        public string $email,
        public string $phone,
        public array $shippingAddress,
        public array $billingAddress,
        public array $items, // [['product_id' => 1, 'qty' => 2], ...]
        public ?string $shippingMethod = null,
        public ?string $paymentMethod = null,
        public ?string $couponCode = null,
        public string $currency = 'VND',
        public ?string $notes = null,
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
        );
    }
}
