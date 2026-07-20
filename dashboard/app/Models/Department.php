<?php

namespace App\Models;

use App\Ecommerce\Department\Enums\RiskLevelThreshold;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'risk_level_threshold',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'risk_level_threshold' => RiskLevelThreshold::class,
    ];

    public function agents(): HasMany
    {
        return $this->hasMany(DepartmentAgent::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(DepartmentAuditLog::class);
    }
}
