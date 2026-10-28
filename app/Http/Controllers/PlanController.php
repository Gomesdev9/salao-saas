<?php

namespace App\Http\Controllers;

use App\Enums\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanController extends Controller
{
    /**
     * "Meu plano": o plano da empresa, o teste grátis e a troca de plano.
     */
    public function edit(Request $request): View
    {
        $salon = $request->user()->salon;

        return view('plan.edit', [
            'salon' => $salon,
            'plans' => Plan::cases(),
            'activeProfessionals' => $salon->activeProfessionalsCount(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::enum(Plan::class)],
        ]);

        $salon = $request->user()->salon;
        $plan = Plan::from($data['plan']);
        $limit = $plan->professionalLimit();
        $active = $salon->activeProfessionalsCount();

        // Não deixa ir para um plano menor do que a equipe ativa de hoje
        if ($limit !== null && $active > $limit) {
            return back()->withErrors([
                'plan' => "O plano {$plan->label()} permite até {$limit} profissionais ativos e hoje você tem {$active}. Deixe alguns como inativos antes de mudar.",
            ]);
        }

        $salon->update(['plan' => $plan]);

        return redirect()->route('plan.edit')->with('status', "Pronto! Sua empresa agora está no plano {$plan->label()}.");
    }
}
