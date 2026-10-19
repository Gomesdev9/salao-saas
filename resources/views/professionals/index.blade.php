<x-page title="Profissionais">
    <x-slot name="actions">
        <x-link-button :href="route('professionals.create')">Novo profissional</x-link-button>
    </x-slot>

    @if ($professionals->isEmpty())
        <p class="p-6 text-gray-600">Nenhum profissional cadastrado ainda.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Telefone</th>
                        <th class="px-4 py-3">Comissão</th>
                        <th class="px-4 py-3">Serviços</th>
                        <th class="px-4 py-3">Situação</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($professionals as $professional)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $professional->name }}</td>
                            <td class="px-4 py-3">{{ $professional->phone }}</td>
                            <td class="px-4 py-3">{{ number_format($professional->commission_percent, 0, ',', '.') }}%</td>
                            <td class="px-4 py-3 text-gray-600">{{ $professional->services->pluck('name')->join(', ') ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $professional->active ? 'Ativo' : 'Inativo' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">
                                <a href="{{ route('professionals.edit', $professional) }}" class="text-indigo-600 hover:text-indigo-800">Editar</a>
                                <x-delete-form :action="route('professionals.destroy', $professional)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-page>
