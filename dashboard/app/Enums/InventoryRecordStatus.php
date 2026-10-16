<?php

namespace App\Enums;

enum InventoryRecordStatus: string
{
    case Draft = 'DRAFT';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}
