<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Lê o mês da URL (?mes=2026-10). Sem mês, ou com um valor inválido, usa o mês atual.
 */
trait ResolvesMonth
{
    protected function month(Request $request): Carbon
    {
        $value = (string) $request->query('mes', $request->input('mes', ''));

        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('!Y-m', $value)->startOfMonth();
            } catch (\Throwable) {
                // cai no mês atual
            }
        }

        return today()->startOfMonth();
    }

    /**
     * O mês para as telas: "2026-10", "Out 2026" e os meses vizinhos (troca de mês ‹ ›).
     *
     * @return array{value: string, label: string, previous: string, next: string}
     */
    protected function monthProps(Carbon $month): array
    {
        return [
            'value' => $month->format('Y-m'),
            'label' => Str::ucfirst($month->translatedFormat('M Y')),
            'previous' => $month->copy()->subMonth()->format('Y-m'),
            'next' => $month->copy()->addMonth()->format('Y-m'),
        ];
    }
}
