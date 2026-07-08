<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class InventorySettings extends Settings
{
    public bool $multi_warehouse_enabled;
    public bool $split_shipping_enabled;
    public string $warehouse_selection_strategy; // proximity, stock_volume

    public int $reservation_expiry_minutes;

    public static function group(): string
    {
        return 'inventory';
    }
}
