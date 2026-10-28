<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
}
