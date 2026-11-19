<?php

namespace App\Ecommerce\Order\Support;

final class OrderItemPayload
{
    public static function fee(string $type, string $name, int | float $amount): array
    {
        return [
            'type' => $type,
            'name' => $name,
            'qty' => 1,
            'unit_price' => $amount,
            'total' => $amount,
        ];
    }
}
