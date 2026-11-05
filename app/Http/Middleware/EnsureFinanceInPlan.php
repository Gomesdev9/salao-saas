<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
            return Inertia::render('finance/upgrade', ['plan' => $salon->plan->label()])
                ->toResponse($request)
                ->setStatusCode(403);
        }

        return $next($request);
    }
}
