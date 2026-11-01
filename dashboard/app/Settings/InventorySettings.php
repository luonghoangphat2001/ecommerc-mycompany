<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class InventorySettings extends Settings
{
    public bool $manage_stock = true;
    public int $low_stock_threshold = 10;
    public bool $notify_low_stock = true;
    public bool $multi_warehouse_enabled = false;
    public bool $split_shipping_enabled = false;
    public string $warehouse_selection_strategy = 'stock_volume'; // proximity, stock_volume
    public int $reservation_expiry_minutes = 15;

    public static function group(): string
    {
        return 'inventory';
    }
}
