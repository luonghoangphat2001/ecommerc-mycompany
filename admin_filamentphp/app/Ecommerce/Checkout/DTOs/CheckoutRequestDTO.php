<?php

namespace App\Ecommerce\Checkout\DTOs;

use App\Ecommerce\Address\DTOs\Address\AddressDTO;

class CheckoutRequestDTO
{
    public function __construct(
        public array $items = [],
        public ?string $shippingMethod = null,
        public ?AddressDTO $shippingAddress = null,
        public ?string $couponCode = null,
        public string $currency = 'VND',
        public ?int $redeemPoints = null,
    ) {}
}
