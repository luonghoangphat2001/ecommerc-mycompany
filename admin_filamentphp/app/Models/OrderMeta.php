<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class OrderMeta extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'shop_order_metas';

    protected $fillable = [
        'order_id',
        'key',
        'value',
    ];

    /** @return BelongsTo<Order,self> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'value'])
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
