<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
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

        return Inertia::render('clients/index', [
            'clients' => [
                'data' => $clients->getCollection()->map(fn (Client $client) => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'whatsapp' => $client->whatsapp,
                    'whatsappUrl' => $client->whatsappUrl(),
                    'birthday' => $client->birthday?->format('d/m'),
                    'birthdaySoon' => $client->hasBirthdayWithin(7),
                    'notes' => $client->notes,
                ]),
                'from' => $clients->firstItem(),
                'to' => $clients->lastItem(),
                'total' => $clients->total(),
                'currentPage' => $clients->currentPage(),
                'prevUrl' => $clients->previousPageUrl(),
                'nextUrl' => $clients->nextPageUrl(),
            ],
            'search' => $search,
            'total' => Client::count(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('clients/form', ['client' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Client::create($this->validated($request));

        return redirect()->route('clients.index')->with('status', 'Cliente cadastrado.');
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('clients/form', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'whatsapp' => $client->whatsapp,
                'birthday' => $client->birthday?->format('Y-m-d'),
                'notes' => $client->notes,
            ],
        ]);
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
