<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSalon;
use Database\Factories\ProfessionalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'commission_percent', 'active'])]
class Professional extends Model
{
    /** @use HasFactory<ProfessionalFactory> */
    use BelongsToSalon, HasFactory;

    protected function casts(): array
    {
        return [
            'commission_percent' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Área principal (a categoria mais comum entre os serviços que faz), ex.: "Cabelo".
     */
    public function area(): ?string
    {
        return $this->services->pluck('category')->filter()->countBy()->sortDesc()->keys()->first();
    }
}
