<?php

namespace Database\Factories;

use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Salon>
 */
class SalonFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Salão '.fake()->firstName();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'phone' => fake()->numerify('(##) 9####-####'),
            'city' => fake()->city(),
        ];
    }
}
