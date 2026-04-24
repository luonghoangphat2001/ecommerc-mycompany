<?php

namespace Database\Factories;

use App\Models\TaxRate;
use App\Models\TaxClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        return [
            'tax_class_id' => TaxClass::factory(),
            'country' => 'VN',
            'name' => 'VAT ' . $this->faker->randomElement([8, 10]) . '%',
            'rate' => $this->faker->randomElement([8.00, 10.00]),
            'priority' => 1,
        ];
    }
}
