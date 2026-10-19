<x-page title="Serviços">
    <x-slot name="actions">
        <x-link-button :href="route('services.create')">Novo serviço</x-link-button>
    </x-slot>

    @if ($services->isEmpty())
        <p class="p-6 text-gray-600">Nenhum serviço cadastrado ainda.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Serviço</th>
                        <th class="px-4 py-3">Categoria</th>
                        <th class="px-4 py-3">Duração</th>
                        <th class="px-4 py-3">Preço</th>
                        <th class="px-4 py-3">Situação</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($services as $service)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $service->name }}</td>
                            <td class="px-4 py-3">{{ $service->category ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $service->duration_minutes }} min</td>
                            <td class="px-4 py-3">R$ {{ number_format($service->price, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $service->active ? 'Ativo' : 'Inativo' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">
                                <a href="{{ route('services.edit', $service) }}" class="text-indigo-600 hover:text-indigo-800">Editar</a>
                                <x-delete-form :action="route('services.destroy', $service)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-page>
