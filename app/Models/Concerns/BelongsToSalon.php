<?php

namespace App\Models\Concerns;

use App\Models\Salon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Isola os dados por salão.
 *
 * Com um usuário logado, toda consulta do model só enxerga registros do
 * salão dele, e todo registro novo já é salvo com o salon_id dele.
 * Assim um salão nunca vê os clientes (ou serviços etc.) de outro.
 */
trait BelongsToSalon
{
    public static function bootBelongsToSalon(): void
    {
        static::addGlobalScope('salon', function (Builder $query) {
            if ($salonId = Auth::user()?->salon_id) {
                $query->where($query->qualifyColumn('salon_id'), $salonId);
            }
        });

        static::creating(function ($model) {
            if (! $model->salon_id && $salonId = Auth::user()?->salon_id) {
                $model->salon_id = $salonId;
            }
        });
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }
}
