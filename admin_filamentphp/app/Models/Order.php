<?php

namespace App\Models;

use App\Ecommerce\Order\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasWebhooks;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    use HasWebhooks;

    /**
     * @var string
     */
    protected $table = 'shop_orders';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'number',
        'subtotal',
        'tax_amount',
        'total',
        'status',
        'currency',
        'exchange_rate',
        'type',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany */
    public function metas()
    {
        return $this->hasMany(OrderMeta::class, 'order_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany */
    public function taxes()
    {
        return $this->hasMany(OrderTax::class, 'order_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne */
    public function shipping()
    {
        return $this->hasOne(OrderShipping::class, 'order_id');
    }

    /** @return HasMany<OrderRefund> */
    public function refunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class, 'order_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'total',
                'tax_amount',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $casts = [
        'status' => OrderStatus::class,
        'subtotal' => 'integer',
        'total' => 'integer',
        'exchange_rate' => 'decimal:4',
    ];

    public function productItems()
    {
        return $this->items()->where('type', 'product');
    }

    public function getSubtotalAttribute(): int
    {
        return (int) ($this->attributes['subtotal'] ?? 0);
    }

    public function getTotalAttribute(): int
    {
        return (int) ($this->attributes['total'] ?? 0);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphMany */
    public function addresses()
    {
        return $this->morphMany(OrderAddress::class, 'addressable');
    }

    /** @return MorphOne<OrderAddress> */
    public function shippingAddress(): MorphOne
    {
        return $this->morphOne(OrderAddress::class, 'addressable')->where('type', 'shipping');
    }

    /** @return MorphOne<OrderAddress> */
    public function billingAddress(): MorphOne
    {
        return $this->morphOne(OrderAddress::class, 'addressable')->where('type', 'billing');
    }

    /** @return BelongsTo<User,self> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<User,self> */
    public function customer(): BelongsTo
    {
        return $this->user();
    }

    public function getCustomerTypeAttribute(): string
    {
        return $this->user_id ? 'Member' : trans('admin.order.guest_customer');
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->shippingAddress) {
            return trim(($this->shippingAddress->first_name ?? '') . ' ' . ($this->shippingAddress->last_name ?? '')) ?: $this->user?->name ?: trans('admin.order.guest_customer');
        }

        return $this->user?->name ?? trans('admin.order.guest_customer');
    }

    /** @return HasMany<OrderItem> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /** @return HasMany<Payment> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeCreatedAtBetween($query, $from = null, $until = null)
    {
        return $query
            ->when($from, fn($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($until, fn($query, $date) => $query->whereDate('created_at', '<=', $date));
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['new', 'processing']);
    }
}
