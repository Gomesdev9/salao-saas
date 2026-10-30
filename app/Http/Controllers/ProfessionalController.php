<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfessionalController extends Controller
{
    public function index(Request $request): Response
    {
        $showInactive = $request->boolean('inativos');

        // O escopo BelongsToSalon já filtra pelo salão do usuário logado
        $professionals = Professional::with('services')
            ->withCount(['appointments as today_count' => fn ($query) => $query
                ->whereBetween('starts_at', [today(), today()->endOfDay()])
                ->whereIn('status', AppointmentStatus::occupying())])
            ->where('active', ! $showInactive)
            ->orderBy('name')
            ->get();

        return Inertia::render('professionals/index', [
            'professionals' => $professionals->map(fn (Professional $professional) => [
                'id' => $professional->id,
                'name' => $professional->name,
                'phone' => $professional->phone,
                'active' => $professional->active,
                // 40,00 vira "40" e 12,50 vira "12,5"
                'commission' => rtrim(rtrim(number_format($professional->commission_percent, 2, ',', ''), '0'), ','),
                'todayCount' => $professional->today_count,
                'services' => $professional->services->pluck('name'),
            ]),
            'showInactive' => $showInactive,
            'activeCount' => Professional::where('active', true)->count(),
            'inactiveCount' => Professional::where('active', false)->count(),
        ]);
    }

    public function create(Request $request): Response
    {
        // No limite do plano, o formulário já vem com "inativo" marcado
        $salon = $request->user()->salon;
        $canActivate = $salon->canActivateProfessional();

        return Inertia::render('professionals/form', [
            'professional' => null,
            'services' => Service::orderBy('name')->get(['id', 'name']),
            'canActivate' => $canActivate,
            'planLimit' => $canActivate ? null : "O plano {$salon->plan->label()} permite até {$salon->plan->professionalLimit()} profissionais ativos, e a sua equipe já chegou nesse número.",
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->ensurePlanAllows($request, $data['active']);

        $professional = Professional::create($data);
        $professional->services()->sync($data['services'] ?? []);

        return redirect()->route('professionals.index')->with('status', 'Profissional cadastrado.');
    }

    public function edit(Professional $professional): Response
    {
        return Inertia::render('professionals/form', [
            'professional' => [
                'id' => $professional->id,
                'name' => $professional->name,
                'phone' => $professional->phone,
                'commission_percent' => (float) $professional->commission_percent,
                'active' => $professional->active,
                'services' => $professional->services()->pluck('services.id'),
            ],
            'services' => Service::orderBy('name')->get(['id', 'name']),
            'canActivate' => true,
            'planLimit' => null,
        ]);
    }

    public function update(Request $request, Professional $professional): RedirectResponse
    {
        $data = $this->validated($request);
        $this->ensurePlanAllows($request, $data['active'] && ! $professional->active);

        $professional->update($data);
        $professional->services()->sync($data['services'] ?? []);

        return redirect()->route('professionals.index')->with('status', 'Profissional atualizado.');
    }

    public function destroy(Professional $professional): RedirectResponse
    {
        $professional->delete();

        return redirect()->route('professionals.index')->with('status', 'Profissional removido.');
    }

    /**
     * Barra um profissional ativo a mais quando a equipe ativa já chegou no limite do plano.
     *
     * @throws ValidationException
     */
    private function ensurePlanAllows(Request $request, bool $activating): void
    {
        $salon = $request->user()->salon;

        if ($activating && ! $salon->canActivateProfessional()) {
            throw ValidationException::withMessages([
                'active' => "O plano {$salon->plan->label()} permite até {$salon->plan->professionalLimit()} profissionais ativos. Salve como inativo ou mude de plano.",
            ]);
        }
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'services' => ['array'],
            // Só aceita serviços do próprio salão
            'services.*' => [Rule::exists('services', 'id')->where('salon_id', $request->user()->salon_id)],
        ]);

        $data['active'] = $request->boolean('active');

        return $data;
    }
}
