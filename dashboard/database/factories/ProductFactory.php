<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Product::class;

    public function definition(): array
    {
        $products = [
            'Máy ảnh Sony A7R IV', 'Ống kính Canon 24-70mm', 'Laptop Dell XPS 13', 
            'Điện thoại iPhone 15 Pro', 'Tai nghe Sony WH-1000XM5', 'Bàn phím cơ Keychron', 
            'Chuột Logitech MX Master 3', 'Màn hình Dell UltraSharp', 'Loa Marshall Stanmore',
            'Sạc dự phòng Anker', 'Balo du lịch North Face', 'Giày chạy bộ Nike Pegasus'
        ];
        
        $name = $this->faker->randomElement($products) . ' ' . $this->faker->randomNumber(5);

        return [
            'shop_brand_id' => Brand::factory(),
            'name' => [
                'vi' => $name,
                'en' => 'Product: ' . $name,
            ],
            'slug' => Str::slug($name),
            'sku' => strtoupper(Str::random(8)),
            'barcode' => $this->faker->ean13(),
            'description' => [
                'vi' => 'Mô tả chi tiết cho sản phẩm: ' . $name,
                'en' => 'Detailed description for product: ' . $name,
            ],
            'qty' => $this->faker->numberBetween(10, 100),
            'security_stock' => 5,
            'featured' => $this->faker->boolean(20),
            'is_visible' => true,
            'old_price' => $this->faker->numberBetween(1000000, 5000000),
            'price' => $this->faker->numberBetween(500000, 4000000),
            'cost' => $this->faker->numberBetween(100000, 1000000),
            'type' => 'deliverable',
            'product_images' => rand(1, 40),
            'published_at' => $this->faker->dateTimeBetween('-1 year', '+1 month'),
        ];
    }
}
