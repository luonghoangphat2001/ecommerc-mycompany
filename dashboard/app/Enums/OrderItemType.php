<?php

namespace App\Enums;

enum OrderItemType: string
{
    case Product = 'product';
    case Shipping = 'shipping';
    case Fee = 'fee';
    case Tax = 'tax';

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Product',
            self::Shipping => 'Shipping',
            self::Fee => 'Fee',
            self::Tax => 'Tax',
        };
    }
}
