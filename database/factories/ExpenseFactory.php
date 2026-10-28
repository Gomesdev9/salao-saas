<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'description' => 'Conta de luz',
            'category' => 'Contas da casa',
            'amount' => 200,
            'due_date' => today(),
            'paid_at' => null,
        ];
    }
}
