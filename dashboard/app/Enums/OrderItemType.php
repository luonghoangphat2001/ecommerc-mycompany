<?php

namespace App\Enums;

enum OrderItemType: string
{
    case Product = 'product';
    case Shipping = 'shipping';
    case Fee = 'fee';
    case Tax = 'tax';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Product => 'Product',
            self::Shipping => 'Shipping',
            self::Fee => 'Fee',
            self::Tax => 'Tax',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Product => 'Sản phẩm',
            self::Shipping => 'Phí vận chuyển',
            self::Fee => 'Phụ phí',
            self::Tax => 'Thuế',
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
