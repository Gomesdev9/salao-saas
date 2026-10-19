<?php

namespace Database\Factories;

use App\Models\Professional;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Professional>
 */
class ProfessionalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'name' => fake()->name(),
            'phone' => fake()->numerify('(##) 9####-####'),
            'commission_percent' => fake()->randomElement([30, 40, 50]),
            'active' => true,
        ];
    }
}
