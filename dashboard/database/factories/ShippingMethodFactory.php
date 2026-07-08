<?php

namespace Database\Factories;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingMethodFactory extends Factory
{
    protected $model = ShippingMethod::class;

    public function definition(): array
    {
        return [
            'shipping_zone_id' => ShippingZone::factory(),
            'name' => $this->faker->randomElement(['Flat Rate Standard', 'Free Shipping', 'Express Delivery']),
            'type' => $this->faker->randomElement(['flat_rate', 'free_shipping']),
            'settings' => ['cost' => 30000],
            'is_enabled' => true,
        ];
    }
}
