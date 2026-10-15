<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Quote> */
class QuoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'number' => 'COT-'.now()->format('Y').'-'.fake()->unique()->numerify('####'),
            'client_id' => Client::factory(),
            'title' => 'Sistema web',
            'currency' => 'PEN',
            'tax_rate' => 0,
            'subtotal' => 5000,
            'total' => 5000,
            'estimated_cost' => 2000,
            'delivery_days' => 20,
        ];
    }

    public function sent(): static
    {
        return $this->state(['status' => 'enviada', 'sent_at' => now(), 'sent_via' => 'correo']);
    }
}
