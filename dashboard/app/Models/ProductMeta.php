<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMeta extends Model
{
    use HasFactory;

    protected $table = 'shop_product_meta';

    protected $fillable = ['shop_product_id', 'key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }
}
