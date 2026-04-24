<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class OrderShipping extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'shop_order_shippings';

    protected $fillable = [
        'order_id',
        'shop_shipping_method_id',
        'method',
        'amount',
        'tax_amount',
        'tracking_number',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'integer',
        'tax_amount' => 'integer',
        'shop_shipping_method_id' => 'integer',
    ];

    /** @return BelongsTo<Order,self> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /** @return BelongsTo<ShippingMethod,self> */
    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shop_shipping_method_id');
    }

    public function tax()
    {
        return $this->hasOne(OrderTax::class, 'order_id', 'order_id')->where('is_shipping', true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['method', 'amount', 'tax_amount', 'tracking_number'])
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
