<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        $categories = ['Điện thoại', 'Máy tính', 'Phụ kiện', 'Giày dép', 'Thời trang', 'Đồ gia dụng', 'Máy ảnh', 'Âm thanh'];
        $name = $this->faker->randomElement($categories);

        return [
            'name' => [
                'vi' => $name,
                'en' => 'Category: ' . $name,
            ],
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'is_visible' => true,
        ];
    }
}
