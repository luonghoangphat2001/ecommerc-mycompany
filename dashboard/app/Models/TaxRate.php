<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    protected $table = 'shop_tax_rates';

    protected $fillable = [
        'tax_class_id',
        'country',
        'state',
        'city',
        'rate',
        'name',
        'priority',
        'is_compound',
        'is_shipping',
    ];

    protected $casts = [
        'is_compound' => 'boolean',
        'is_shipping' => 'boolean',
        'rate' => 'decimal:4',
    ];

    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class, 'tax_class_id');
    }
}
