<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $table = 'shop_coupons';

    protected $fillable = [
        'code',
        'type',
        'amount',
        'minimum_order_amount',
        'limit_usage_to_x_items',
        'usage_limit',
        'usage_limit_per_user',
        'usage_count',
        'expiry_date',
        'individual_use',
        'exclude_sale_items',
        'product_ids',
        'excluded_product_ids',
        'category_ids',
        'excluded_category_ids',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'limit_usage_to_x_items' => 'integer',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
        'usage_count' => 'integer',
        'expiry_date' => 'datetime',
        'individual_use' => 'boolean',
        'exclude_sale_items' => 'boolean',
        'product_ids' => 'array',
        'excluded_product_ids' => 'array',
        'category_ids' => 'array',
        'excluded_category_ids' => 'array',
        'is_active' => 'boolean',
    ];
}
