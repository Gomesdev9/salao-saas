<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Models\Concerns\BelongsToSalon;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['salon_id', 'description', 'category', 'method', 'amount', 'due_date', 'paid_at'])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use BelongsToSalon, HasFactory;

    public const CATEGORIES = ['Contas da casa', 'Aluguel', 'Produtos', 'Marketing', 'Serviços', 'Outros'];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'date',
        ];
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function isOverdue(): bool
    {
        return ! $this->isPaid() && $this->due_date->lt(today());
    }
}
