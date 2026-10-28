<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSalon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Comissão paga no fechamento do mês ("Marcar como pago").
 */
#[Fillable(['salon_id', 'professional_id', 'month', 'amount', 'paid_at'])]
class CommissionPayout extends Model
{
    use BelongsToSalon;

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }
}
