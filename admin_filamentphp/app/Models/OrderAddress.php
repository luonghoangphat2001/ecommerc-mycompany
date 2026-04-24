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
        'addressable_type',
        'addressable_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'phone',
        'email',
        'country_code',
        'address_detail',
        'city_id',
        'state_id',
        'ward_id',
        'postal_code',
    ];

    /** @return MorphTo<Model,self> */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Redirect activity subject to the parent model (usually Order) to keep timeline unified.
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        if ($this->addressable_type === Order::class) {
            $activity->subject_id = $this->addressable_id;
            $activity->subject_type = Order::class;
        }
    }
}
