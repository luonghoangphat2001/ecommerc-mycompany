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
        'department_id',
        'department_agent_id',
        'event_id',
        'action',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(DepartmentAgent::class, 'department_agent_id');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
