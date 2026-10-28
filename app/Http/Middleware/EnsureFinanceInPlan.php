<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Telas do Financeiro: quem está num plano sem Financeiro vê o convite para mudar de plano.
 */
class EnsureFinanceInPlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $salon = $request->user()->salon;

        if (! $salon->hasFinance()) {
            return response()->view('finance.upgrade', ['salon' => $salon], 403);
        }

        return $next($request);
    }
}
