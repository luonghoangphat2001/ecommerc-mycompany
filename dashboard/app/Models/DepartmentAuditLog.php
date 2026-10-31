<?php

namespace App\Models;

use App\Enums\WebhookLogStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentAuditLog extends Model
{
    use HasFactory;

    protected $table = 'webhook_logs';

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
        'status' => WebhookLogStatus::class,
        'payload' => 'array',
        'response' => 'array',
        'duration' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('department', function (Builder $builder) {
            $builder->whereNotNull('department_id');
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(DepartmentAgent::class, 'department_agent_id');
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
