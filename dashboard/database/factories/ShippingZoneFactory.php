<?php

namespace Database\Factories;

use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingZoneFactory extends Factory
{
    protected $model = ShippingZone::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Vietnam', 'Rest of World', 'Europe', 'North America']),
            'locations' => [['country' => 'VN', 'state' => '*']],
            'sort' => 0,
        ];
    }
}
