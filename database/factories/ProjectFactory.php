<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'PRY-'.now()->format('Y').'-'.fake()->unique()->numerify('####'),
            'name' => fake()->catchPhrase(),
            'client_id' => Client::factory(),
            'status' => 'en_ejecucion',
            'start_date' => now()->subDays(10),
            'internal_due_date' => now()->addDays(20),
            'contract_due_date' => now()->addDays(30),
            'contract_amount' => 10000,
        ];
    }
}
