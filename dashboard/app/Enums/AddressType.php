<?php

namespace App\Enums;

enum AddressType: string
{
    case Shipping = 'shipping';
    case Billing = 'billing';

    public function label(): string
    {
        return match ($this) {
            self::Shipping => __('admin.order.shipping_address') ?? 'Shipping Address',
            self::Billing => __('admin.order.billing_address') ?? 'Billing Address',
        };
    }
}
