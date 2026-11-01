<?php

namespace App\Enums;

enum ShippingMethodType: string
{
    case FlatRate = 'flat_rate';
    case FreeShipping = 'free_shipping';
    case LocalPickup = 'local_pickup';
    case WeightBased = 'weight_based';
    case Carrier = 'carrier';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::FlatRate => 'Flat rate',
            self::FreeShipping => 'Free shipping',
            self::LocalPickup => 'Local pickup',
            self::WeightBased => 'Weight based',
            self::Carrier => 'Carrier API',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::FlatRate => 'Đồng giá',
            self::FreeShipping => 'Miễn phí vận chuyển',
            self::LocalPickup => 'Nhận hàng tại cửa hàng',
            self::WeightBased => 'Theo khối lượng',
            self::Carrier => 'API Hãng vận chuyển',
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
