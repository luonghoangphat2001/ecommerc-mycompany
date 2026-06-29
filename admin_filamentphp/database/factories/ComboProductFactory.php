<?php

namespace Database\Factories;

use App\Models\ComboProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ComboProductFactory extends Factory
{
    protected $model = ComboProduct::class;

    public function definition(): array
    {
        $names = [
            'Combo Khởi Nghiệp',
            'Combo Gia Đình',
            'Combo Văn Phòng',
            'Combo Học Tập',
            'Combo Du Lịch',
            'Combo Thể Thao',
            'Combo Làm Đẹp',
            'Combo Sức Khỏe',
        ];

        $name = $this->faker->randomElement($names) . ' ' . $this->faker->randomNumber(2);
        $comboPrice = $this->faker->randomElement([500000, 800000, 1200000, 2000000, 3500000]);
        $originalPrice = (int) ($comboPrice * (1 + $this->faker->randomElement([15, 20, 25, 30]) / 100));
        $discountPercent = (int) ((($originalPrice - $comboPrice) / $originalPrice) * 100);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(4),
            'description' => $this->faker->paragraph(),
            'combo_price' => $comboPrice,
            'original_price' => $originalPrice,
            'discount_percent' => $discountPercent,
            'is_active' => true,
            'start_date' => now()->subDays(7),
            'end_date' => $this->faker->randomElement([null, now()->addMonths(3)]),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function inactive(): self
    {
        return $this->state([
            'is_active' => false,
        ]);
    }

    public function expired(): self
    {
        return $this->state([
            'end_date' => now()->subDay(),
        ]);
    }
}
