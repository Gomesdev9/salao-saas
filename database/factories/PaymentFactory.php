<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use App\Models\Professional;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'professional_id' => fn (array $p) => Professional::factory()->create(['salon_id' => $p['salon_id'], 'commission_percent' => 40]),
            'description' => fake()->name().' — Corte',
            'method' => PaymentMethod::Pix,
            'subtotal' => 100,
            'discount_percent' => 0,
            'amount' => 100,
            'installments' => 1,
            'commission_percent' => 40,
            'commission' => 40,
            'paid_at' => now(),
        ];
    }
}
