<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentPayroll extends Model
{
    protected $fillable = [
        'department_id',
        'user_id',
        'month',
        'base_salary',
        'allowance',
        'tax',
        'insurance',
        'net_salary',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
