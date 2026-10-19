<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('busca'));

        $clients = Client::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', compact('clients', 'search'));
    }

    public function create(): View
    {
        return view('clients.create', ['client' => new Client()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Client::create($this->validated($request));

        return redirect()->route('clients.index')->with('status', 'Cliente cadastrado.');
    }

    public function edit(Client $client): View
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validated($request));

        return redirect()->route('clients.index')->with('status', 'Cliente atualizado.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Cliente removido.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
