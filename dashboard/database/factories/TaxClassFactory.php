<?php

namespace Database\Factories;

use App\Models\TaxClass;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TaxClassFactory extends Factory
{
    protected $model = TaxClass::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement(['Standard', 'Reduced', 'Zero Rate']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
