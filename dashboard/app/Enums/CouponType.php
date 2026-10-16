<?php

namespace App\Enums;

enum CouponType: string
{
    case FixedCart = 'fixed_cart';
    case Percentage = 'percentage';
    case FixedProduct = 'fixed_product';

    public function label(): string
    {
        return match ($this) {
            self::FixedCart => __('admin.coupon.types.fixed_cart') ?? 'Fixed Cart Discount',
            self::Percentage => __('admin.coupon.types.percentage') ?? 'Percentage Discount',
            self::FixedProduct => __('admin.coupon.types.fixed_product') ?? 'Fixed Product Discount',
        };
    }
}
