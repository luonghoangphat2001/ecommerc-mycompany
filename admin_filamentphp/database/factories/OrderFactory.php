<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'number' => 'OR-' . strtoupper(Str::random(8)),
            'user_id' => null,
            'subtotal' => 0, // Will be calculated
            'total' => 0,    // Will be calculated
            'status' => $this->faker->randomElement(OrderStatus::class),
            'currency' => 'VND',
            'exchange_rate' => 1.0,
            'type' => 'shop',
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
