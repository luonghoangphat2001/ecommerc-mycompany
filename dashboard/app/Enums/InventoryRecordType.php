<?php

namespace App\Enums;

enum InventoryRecordType: string
{
    case In = 'IN';
    case Out = 'OUT';
    case Transfer = 'TRANSFER';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Stock In',
            self::Out => 'Stock Out',
            self::Transfer => 'Transfer',
        };
    }
}
