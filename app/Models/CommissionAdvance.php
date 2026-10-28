<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Models\Concerns\BelongsToSalon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vale: parte da comissão adiantada ao profissional antes do fechamento do mês.
 */
#[Fillable(['salon_id', 'professional_id', 'amount', 'method', 'given_on'])]
class CommissionAdvance extends Model
{
    use BelongsToSalon;

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'given_on' => 'date',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }
}
