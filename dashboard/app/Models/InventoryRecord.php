<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryRecord extends Model
{
    protected $table = 'shop_inventory_records';

    protected $fillable = [
        'type',
        'status',
        'notes',
    ];

    protected $casts = [
        'type' => \App\Enums\InventoryRecordType::class,
        'status' => \App\Enums\InventoryRecordStatus::class,
    ];

    /**
     * Relate child items.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(InventoryRecordItem::class, 'warehouse_record_id');
    }
}
