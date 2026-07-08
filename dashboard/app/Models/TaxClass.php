<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxClass extends Model
{
    use HasFactory;

    protected $table = 'shop_tax_classes';

    protected $fillable = ['name', 'slug'];

    public function rates()
    {
        return $this->hasMany(TaxRate::class, 'tax_class_id');
    }
}
