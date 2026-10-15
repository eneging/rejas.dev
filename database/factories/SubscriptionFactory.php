<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subscription> */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'service' => 'Soporte mensual',
            'amount' => 300,
            'starts_at' => now()->startOfMonth(),
            'next_due_date' => now()->startOfMonth(),
            'status' => 'activa',
        ];
    }
}
