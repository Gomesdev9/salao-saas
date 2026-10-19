<x-page title="Clientes">
    <x-slot name="actions">
        <x-link-button :href="route('clients.create')">Novo cliente</x-link-button>
    </x-slot>

    <form method="GET" action="{{ route('clients.index') }}" class="flex gap-2 border-b border-gray-100 p-4">
        <x-text-input name="busca" type="search" class="block w-full" :value="$search" placeholder="Buscar por nome ou WhatsApp" />
        <x-primary-button>Buscar</x-primary-button>
    </form>

    @if ($clients->isEmpty())
        <p class="p-6 text-gray-600">{{ $search ? 'Nenhum cliente encontrado.' : 'Nenhum cliente cadastrado ainda.' }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">WhatsApp</th>
                        <th class="px-4 py-3">Aniversário</th>
                        <th class="px-4 py-3">Observações</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($clients as $client)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $client->name }}</td>
                            <td class="px-4 py-3">{{ $client->whatsapp }}</td>
                            <td class="px-4 py-3">{{ $client->birthday?->format('d/m') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \Illuminate\Support\Str::limit($client->notes, 40) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">
                                <a href="{{ route('clients.edit', $client) }}" class="text-indigo-600 hover:text-indigo-800">Editar</a>
                                <x-delete-form :action="route('clients.destroy', $client)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $clients->links() }}</div>
    @endif
</x-page>
