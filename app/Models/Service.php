<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSalon;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'category', 'duration_minutes', 'price', 'active'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use BelongsToSalon, HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function professionals(): BelongsToMany
    {
        return $this->belongsToMany(Professional::class);
    }
}
