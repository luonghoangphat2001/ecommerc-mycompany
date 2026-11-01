<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentPurchaseOrder extends Model
{
    protected $fillable = [
        'department_id',
        'po_number',
        'supplier_name',
        'status',
        'total_amount',
        'expected_delivery_date',
    ];

    protected $casts = [
        'status' => PurchaseOrderStatus::class,
        'expected_delivery_date' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
