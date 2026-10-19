<?php

namespace Database\Factories;

use App\Models\Salon;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'name' => fake()->randomElement(['Corte feminino', 'Escova', 'Manicure', 'Pedicure', 'Coloração', 'Design de sobrancelha']),
            'category' => fake()->randomElement(['Cabelo', 'Unhas', 'Estética']),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'price' => fake()->randomElement([35, 50, 80, 120]),
            'active' => true,
        ];
    }
}
