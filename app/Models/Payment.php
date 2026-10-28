<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Models\Concerns\BelongsToSalon;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['salon_id', 'appointment_id', 'professional_id', 'description', 'method', 'subtotal', 'discount_percent', 'amount', 'installments', 'cash_received', 'commission_percent', 'commission', 'paid_at'])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use BelongsToSalon, HasFactory;

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'subtotal' => 'decimal:2',
            'discount_percent' => 'integer',
            'amount' => 'decimal:2',
            'installments' => 'integer',
            'cash_received' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'commission' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function discount(): float
    {
        return (float) $this->subtotal - (float) $this->amount;
    }

    public function change(): ?float
    {
        return $this->cash_received !== null ? (float) $this->cash_received - (float) $this->amount : null;
    }
}
