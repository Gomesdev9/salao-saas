<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::orderBy('category')->orderBy('name')->get();

        return view('services.index', compact('services'));
    }

    public function create(): View
    {
        return view('services.create', ['service' => new Service(['active' => true, 'duration_minutes' => 30])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Service::create($this->validated($request));

        return redirect()->route('services.index')->with('status', 'Serviço cadastrado.');
    }

    public function edit(Service $service): View
    {
        return view('services.edit', compact('service'));
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
