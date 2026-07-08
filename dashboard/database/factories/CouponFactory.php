<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        $types = ['fixed_cart', 'percentage', 'fixed_product'];
        $type = $this->faker->randomElement($types);

        return [
            'code' => strtoupper($this->faker->unique()->bothify('COUPON####')),
            'type' => $type,
            'amount' => $type === 'percentage' ? $this->faker->randomElement([10, 15, 20, 25, 30]) : $this->faker->randomElement([50000, 100000, 200000, 500000]),
            'minimum_order_amount' => $this->faker->randomElement([null, 100000, 200000, 500000]),
            'limit_usage_to_x_items' => $this->faker->randomElement([null, 1, 2, 3]),
            'usage_limit' => $this->faker->randomElement([null, 50, 100, 200]),
            'usage_limit_per_user' => $this->faker->randomElement([null, 1, 2, 5]),
            'usage_count' => 0,
            'expiry_date' => $this->faker->randomElement([null, now()->addDays(30), now()->addDays(60), now()->addMonths(3)]),
            'individual_use' => $this->faker->boolean(20),
            'exclude_sale_items' => $this->faker->boolean(30),
            'product_ids' => null,
            'excluded_product_ids' => null,
            'category_ids' => null,
            'excluded_category_ids' => null,
            'is_active' => true,
        ];
    }

    public function fixedCart(int $amount): self
    {
        return $this->state([
            'type' => 'fixed_cart',
            'amount' => $amount,
        ]);
    }

    public function percentage(int $percent): self
    {
        return $this->state([
            'type' => 'percentage',
            'amount' => $percent,
        ]);
    }

    public function expired(): self
    {
        return $this->state([
            'expiry_date' => now()->subDays(7),
        ]);
    }

    public function limitedUsage(int $limit): self
    {
        return $this->state([
            'usage_limit' => $limit,
        ]);
    }
}
