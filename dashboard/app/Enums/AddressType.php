<?php

namespace App\Enums;

enum AddressType: string
{
    case Shipping = 'shipping';
    case Billing = 'billing';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Shipping => 'Shipping Address',
            self::Billing => 'Billing Address',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Shipping => 'Địa chỉ giao hàng',
            self::Billing => 'Địa chỉ thanh toán',
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
