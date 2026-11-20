<?php

namespace App\Models;

use App\Ecommerce\Order\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'discount_total',
        'loyalty_discount_total',
        'shipping_total',
        'tax_amount',
        'manual_tax_amount',
        'redeemed_points',
        'earned_points',
        'internal_note',
        'customer_notes',
        'cancellation_reason',
        'cancelled_at',
        'total',
        'status',
        'currency',
        'exchange_rate',
        'type',
    ];

    /** @return HasMany<OrderTax> */
    public function taxes(): HasMany
    {
        return $this->hasMany(OrderTax::class, 'order_id');
    }

    /** @return HasOne<OrderShipping> */
    public function shipping(): HasOne
    {
        return $this->hasOne(OrderShipping::class, 'order_id');
    }

    /** @return HasMany<OrderRefund> */
    public function refunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class, 'order_id');
    }

    /** @return HasMany<OrderCoupon> */
    public function coupons(): HasMany
    {
        return $this->hasMany(OrderCoupon::class, 'order_id');
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
        'discount_total' => 'integer',
        'loyalty_discount_total' => 'integer',
        'shipping_total' => 'integer',
        'tax_amount' => 'integer',
        'manual_tax_amount' => 'integer',
        'redeemed_points' => 'integer',
        'earned_points' => 'integer',
        'cancelled_at' => 'datetime',
        'total' => 'integer',
        'exchange_rate' => 'decimal:4',
    ];

    public function getSubtotalAttribute(): int
    {
        return (int) ($this->attributes['subtotal'] ?? 0);
    }

    public function getTotalAttribute(): int
    {
        return (int) ($this->attributes['total'] ?? 0);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany */
    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderAddress::class, 'order_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<OrderAddress> */
    public function shippingAddress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OrderAddress::class, 'order_id')->where('type', \App\Enums\AddressType::Shipping->value);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<OrderAddress> */
    public function billingAddress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OrderAddress::class, 'order_id')->where('type', \App\Enums\AddressType::Billing->value);
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

    public function inventoryMovements(): MorphMany
    {
        return $this->morphMany(InventoryMovement::class, 'source');
    }

    /** @return HasMany<Payment> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return MorphMany<PaymentData, $this> */
    public function paymentData(): MorphMany
    {
        return $this->morphMany(PaymentData::class, 'paymentable');
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
        return $query->whereIn('status', [
            OrderStatus::New->value,
            OrderStatus::Processing->value,
        ]);
    }
}
