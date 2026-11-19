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

    public function mainWarehouse(): static
    {
        return $this->state(['name' => 'Kho Tổng', 'slug' => 'wh-main', 'location' => 'Khu công nghiệp Tân Tạo, TP.HCM']);
    }

    public function hcmWarehouse(): static
    {
        return $this->state(['name' => 'Kho TP.HCM', 'slug' => 'wh-hcm', 'location' => 'Quận 7, TP.HCM']);
    }

    public function hnWarehouse(): static
    {
        return $this->state(['name' => 'Kho Hà Nội', 'slug' => 'wh-hn', 'location' => 'Quận Long Biên, Hà Nội']);
    }
}
