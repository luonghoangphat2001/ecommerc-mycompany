<?php

namespace Database\Factories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        $locations = [
            'Kho chính Hà Nội',
            'Kho TP.HCM',
            'Kho Đà Nẵng',
            'Kho Hải Phòng',
            'Kho Cần Thơ',
        ];

        $name = $this->faker->randomElement($locations);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(4),
            'location' => $this->faker->address(),
            'is_active' => true,
        ];
    }
}
