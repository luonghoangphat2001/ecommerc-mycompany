<?php

namespace App\Models;

use App\Ecommerce\Department\Enums\AgentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'agent_code',
        'name',
        'api_token_hash',
        'webhook_secret',
        'status',
        'last_active_at',
    ];

    protected $casts = [
        'status' => AgentStatus::class,
        'last_active_at' => 'datetime',
        'api_token_hash' => 'hashed',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
