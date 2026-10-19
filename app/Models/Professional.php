<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSalon;
use Database\Factories\ProfessionalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
