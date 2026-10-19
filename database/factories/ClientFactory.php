<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'name' => fake()->name(),
            'whatsapp' => fake()->numerify('(##) 9####-####'),
            'birthday' => fake()->optional()->date(),
            'notes' => null,
        ];
    }
}
