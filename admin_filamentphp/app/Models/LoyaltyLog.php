<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyLog extends Model
{
    protected $table = 'shop_loyalty_logs';

    protected $fillable = [
        'user_id',
        'points_changed',
        'action_type',
        'order_id',
        'expired_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
