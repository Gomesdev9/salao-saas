<?php

namespace Database\Factories;

use App\Enums\BusinessType;
use App\Enums\Plan;
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
            'business_type' => BusinessType::BeautySalon,
            'plan' => Plan::Professional,
            'trial_ends_at' => now()->addDays(Plan::TRIAL_DAYS),
            'phone' => fake()->numerify('(##) 9####-####'),
            'city' => fake()->city(),
        ];
    }
}
