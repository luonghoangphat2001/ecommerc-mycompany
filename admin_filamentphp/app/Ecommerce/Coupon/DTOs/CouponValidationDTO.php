<?php

namespace App\Ecommerce\Coupon\DTOs;

use App\Models\Coupon;

class CouponValidationDTO
{
    public function __construct(
        public Coupon $coupon,
        public array $items = [],
        public float $subtotal = 0.0,
        public ?int $userId = null
    ) {}
}
