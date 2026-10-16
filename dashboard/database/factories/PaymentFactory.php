<?php

namespace Database\Factories;

use Akaunting\Money\Currency;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'reference' => 'PAY' . $this->faker->unique()->randomNumber(6),
            'currency' => $this->faker->randomElement(collect(Currency::getCurrencies())->keys()),
            'amount' => $this->faker->randomFloat(2, 100, 2000),
            'provider' => $this->faker->randomElement(['stripe', 'paypal']),
            'method' => $this->faker->randomElement([\App\Enums\PaymentMethod::CreditCard, \App\Enums\PaymentMethod::BankTransfer, \App\Enums\PaymentMethod::Paypal]),
            'status' => \App\Enums\PaymentStatus::Completed,
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-6 month'),
            'updated_at' => $this->faker->dateTimeBetween('-5 month', 'now'),
        ];
    }
}
