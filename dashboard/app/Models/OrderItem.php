<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'shop_order_items';

    protected $fillable = [
        'order_id',
        'shop_product_id',
        'type',
        'name',
        'qty',
        'unit_price',
        'total',
        'metadata',
        'sort',
    ];

    protected $casts = [
        'metadata' => 'array',
        'unit_price' => 'integer',
        'total' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function tax()
    {
        return $this->hasOne(OrderTax::class, 'shop_order_item_id');
    }
}
