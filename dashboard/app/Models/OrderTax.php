<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class OrderTax extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'shop_order_taxes';

    protected $fillable = [
        'order_id',
        'shop_order_item_id',
        'is_shipping',
        'shop_tax_rate_id',
        'name',
        'amount',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'integer',
        'shop_tax_rate_id' => 'integer',
    ];

    /** @return BelongsTo<Order,self> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /** @return BelongsTo<TaxRate,self> */
    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'shop_tax_rate_id');
    }

    /** @return BelongsTo<OrderItem,self> */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'shop_order_item_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Redirect activity subject to the parent Order to keep timeline unified.
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->subject_id = $this->order_id;
        $activity->subject_type = Order::class;
    }
}
