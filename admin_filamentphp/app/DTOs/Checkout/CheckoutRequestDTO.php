<?php

namespace App\DTOs\Checkout;

use App\DTOs\Address\AddressDTO;

class CheckoutRequestDTO
{
    public function __construct(
        public array $items = [],
        public ?string $shippingMethod = null,
        public ?AddressDTO $shippingAddress = null,
        public ?string $couponCode = null,
        public string $currency = 'VND',
    ) {}
}
