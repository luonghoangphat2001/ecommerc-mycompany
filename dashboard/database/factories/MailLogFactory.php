<?php

namespace Database\Factories;

use App\Models\MailLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class MailLogFactory extends Factory
{
    protected $model = MailLog::class;

    public function definition(): array
    {
        return [
            'from' => 'system@example.com',
            'to' => $this->faker->safeEmail(),
            'subject' => $this->faker->sentence(),
            'body' => $this->faker->paragraphs(3, true),
            'status' => 'sent',
            'delivered' => true,
            'opened' => $this->faker->boolean(40),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
