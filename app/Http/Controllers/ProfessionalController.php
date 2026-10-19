<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfessionalController extends Controller
{
    public function index(): View
    {
        // O escopo BelongsToSalon já filtra pelo salão do usuário logado
        $professionals = Professional::with('services')->orderBy('name')->get();

        return view('professionals.index', compact('professionals'));
    }

    public function create(): View
    {
        return view('professionals.create', [
            'professional' => new Professional(['active' => true, 'commission_percent' => 0]),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $professional = Professional::create($data);
        $professional->services()->sync($data['services'] ?? []);

        return redirect()->route('professionals.index')->with('status', 'Profissional cadastrado.');
    }

    public function edit(Professional $professional): View
    {
        return view('professionals.edit', [
            'professional' => $professional->load('services'),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Professional $professional): RedirectResponse
    {
        $data = $this->validated($request);

        $professional->update($data);
        $professional->services()->sync($data['services'] ?? []);

        return redirect()->route('professionals.index')->with('status', 'Profissional atualizado.');
    }

    public function destroy(Professional $professional): RedirectResponse
    {
        $professional->delete();

        return redirect()->route('professionals.index')->with('status', 'Profissional removido.');
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
