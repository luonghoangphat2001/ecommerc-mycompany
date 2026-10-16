<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'shop_payments';

    protected $fillable = [
        'order_id',
        'method',
        'status',
        'currency',
        'amount',
        'provider',
        'reference',
    ];

    protected $casts = [
        'status' => \App\Enums\PaymentStatus::class,
        'method' => \App\Enums\PaymentMethod::class,
        'amount' => 'integer',
    ];

    /** @return BelongsTo<Order,self> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
