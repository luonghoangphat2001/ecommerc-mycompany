<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'shop_product_id' => Product::factory(),
            'qty' => $this->faker->numberBetween(1, 5),
            'unit_price' => $this->faker->numberBetween(10000, 1000000),
            'total' => 0, // Will be calculated
            'type' => 'product',
            'name' => null, // Will be filled from product name or custom
        ];
    }
}
