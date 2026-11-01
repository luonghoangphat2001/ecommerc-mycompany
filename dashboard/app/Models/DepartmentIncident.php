<?php

namespace App\Models;

use App\Enums\IncidentStatus;
use App\Enums\IncidentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentIncident extends Model
{
    protected $fillable = [
        'department_id',
        'order_id',
        'assignee_id',
        'type',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => IncidentStatus::class,
        'type' => IncidentType::class,
    ];

    public function getTypeLabelAttribute(): string
    {
        $val = $this->type instanceof IncidentType ? $this->type->value : (string) $this->type;
        $key = 'workspace.ops.incident_types.' . $val;
        $label = __($key);

        return $label !== $key ? $label : ucwords(str_replace('_', ' ', $val));
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
