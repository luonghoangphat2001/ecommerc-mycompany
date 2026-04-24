<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response',
        'status',
        'duration',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'duration' => 'integer',
        'created_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
