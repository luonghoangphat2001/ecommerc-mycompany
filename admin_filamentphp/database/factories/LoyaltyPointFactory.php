<?php

namespace Database\Factories;

use App\Models\LoyaltyPoint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyPointFactory extends Factory
{
    protected $model = LoyaltyPoint::class;

    public function definition(): array
    {
        $currentPoints = $this->faker->numberBetween(0, 5000);
        $lifetimePoints = $currentPoints + $this->faker->numberBetween(0, 2000);

        return [
            'user_id' => User::factory(),
            'current_points' => $currentPoints,
            'lifetime_points' => $lifetimePoints,
        ];
    }
}
