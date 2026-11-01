<?php

namespace App\Enums;

enum CouponType: string
{
    case FixedCart = 'fixed_cart';
    case Percentage = 'percentage';
    case FixedProduct = 'fixed_product';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::FixedCart => 'Fixed Cart Discount',
            self::Percentage => 'Percentage Discount',
            self::FixedProduct => 'Fixed Product Discount',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::FixedCart => 'Giảm giá cố định theo giỏ hàng',
            self::Percentage => 'Giảm giá theo phần trăm',
            self::FixedProduct => 'Giảm giá cố định cho sản phẩm',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function optionsVi(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->labelVi();
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
