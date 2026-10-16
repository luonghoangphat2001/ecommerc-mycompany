<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class OrderAddress extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'shop_order_addresses';

    protected $fillable = [
        'order_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'phone',
        'email',
        'country_code',
        'address_detail',
        'address_line_2',
        'city_id',
        'state_id',
        'ward_id',
        'postal_code',
    ];

    protected $casts = [
        'type' => \App\Enums\AddressType::class,
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Order,self> */
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Backward-compatibility alias for previous morph relation.
     */
    public function addressable()
    {
        return $this->order();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Redirect activity subject to the parent model (Order) to keep timeline unified.
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        if ($this->order_id) {
            $activity->subject_id = $this->order_id;
            $activity->subject_type = Order::class;
        }
    }
}
