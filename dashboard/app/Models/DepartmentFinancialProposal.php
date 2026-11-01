<?php

namespace App\Models;

use App\Enums\FinancialProposalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentFinancialProposal extends Model
{
    protected $fillable = [
        'department_id',
        'user_id',
        'title',
        'reason',
        'amount',
        'status',
        'is_urgent',
    ];

    protected $casts = [
        'status' => FinancialProposalStatus::class,
        'is_urgent' => 'boolean',
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
