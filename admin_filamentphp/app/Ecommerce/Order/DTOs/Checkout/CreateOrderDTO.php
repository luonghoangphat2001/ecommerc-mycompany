<?php

namespace App\Ecommerce\Order\DTOs\Checkout;

class CreateOrderDTO
{
    public function __construct(
        public readonly array $data,
        public readonly array $items = []
    ) {}

    public static function fromArray(array $data, array $items = []): self
    {
        return new self($data, $items);
    }
}
