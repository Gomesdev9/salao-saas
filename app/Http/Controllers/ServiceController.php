<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $category = $request->query('categoria');
        $services = Service::orderBy('name')->get();

        // Agrupa por categoria; serviço sem categoria vai para "Outros"
        $groups = $services
            ->groupBy(fn (Service $service) => $service->category ?: 'Outros')
            ->toBase()
            ->sortKeys();

        return Inertia::render('services/index', [
            'categories' => $groups->keys(),
            'category' => $groups->has($category) ? $category : null,
            'groups' => ($groups->has($category) ? $groups->only($category) : $groups)
                ->map(fn ($services, $name) => [
                    'name' => $name,
                    'services' => $services->map(fn (Service $service) => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'duration_minutes' => $service->duration_minutes,
                        'price' => (float) $service->price,
                        'active' => $service->active,
                    ])->values(),
                ])
                ->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('services/form', [
            'service' => null,
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Service::create($this->validated($request));

        return redirect()->route('services.index')->with('status', 'Serviço cadastrado.');
    }

    public function edit(Service $service): Response
    {
        return Inertia::render('services/form', [
            'service' => $service->only(['id', 'name', 'category', 'duration_minutes', 'price', 'active']),
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validated($request));

        return redirect()->route('services.index')->with('status', 'Serviço atualizado.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('services.index')->with('status', 'Serviço removido.');
    }

    /**
     * Categorias já usadas no salão, para sugerir no formulário.
     */
    private function categories(): array
    {
        return Service::whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:600'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $data['active'] = $request->boolean('active');

        return $data;
    }
}
