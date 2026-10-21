<x-page title="Clientes" :subtitle="$total.' '.($total === 1 ? 'cliente cadastrado' : 'clientes cadastrados')">
    <x-slot name="actions">
        <x-link-button :href="route('clients.create')" icon="mais">Novo cliente</x-link-button>
    </x-slot>

    <div class="cartao overflow-hidden">
        <form method="GET" action="{{ route('clients.index') }}" class="border-b border-linha px-5 py-4">
            <label class="flex h-11 items-center gap-2.5 rounded-campo border border-linha-forte bg-cartao px-3.5 text-suave focus-within:border-caramelo focus-within:ring-1 focus-within:ring-caramelo">
                <x-icon name="busca" class="h-[18px] w-[18px]" />
                <span class="sr-only">Buscar cliente</span>
                <input type="search" name="busca" value="{{ $search }}" placeholder="Buscar por nome ou WhatsApp"
                       class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-[15px] text-tinta placeholder:text-suave/70 focus:ring-0">
            </label>
        </form>

        @if ($clients->isEmpty())
            <p class="px-5 py-8 text-center text-suave">{{ $search ? 'Nenhum cliente encontrado.' : 'Nenhum cliente cadastrado ainda.' }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] border-collapse text-sm">
                    <thead>
                        <tr class="bg-areia text-left text-xs uppercase tracking-wider text-suave">
                            <th class="px-5 py-3 font-semibold">Nome</th>
                            <th class="px-3 py-3 font-semibold">WhatsApp</th>
                            <th class="px-3 py-3 font-semibold">Aniversário</th>
                            <th class="px-3 py-3 font-semibold">Observações</th>
                            <th class="px-5 py-3"><span class="sr-only">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clients as $client)
                            <tr class="border-t border-linha-clara">
                                <td class="px-5 py-2.5">
                                    <span class="flex items-center gap-2.5">
                                        <x-avatar :name="$client->name" />
                                        <span class="font-semibold">{{ $client->name }}</span>
                                    </span>
                                </td>
                                <td class="px-3 py-2.5">
                                    @if ($url = $client->whatsappUrl())
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="font-medium text-cafe hover:text-cafe-escuro">{{ $client->whatsapp }}</a>
                                    @else
                                        {{ $client->whatsapp ?: '—' }}
                                    @endif
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="inline-flex items-center gap-1.5">
                                        {{ $client->birthday?->format('d/m') ?? '—' }}
                                        @if ($client->hasBirthdayWithin(7))
                                            <span class="rounded-full bg-mel px-2 py-0.5 text-xs font-semibold text-mel-texto">esta semana</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="max-w-[260px] truncate px-3 py-2.5 text-suave">{{ $client->notes ?: '—' }}</td>
                                <td class="px-5 py-1 text-right">
                                    <a href="{{ route('clients.edit', $client) }}" class="inline-flex min-h-11 items-center font-semibold text-cafe hover:text-cafe-escuro">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-linha px-5 py-3 text-sm text-suave">
                <span>Mostrando {{ $clients->firstItem() }}–{{ $clients->lastItem() }} de {{ $clients->total() }}</span>
                @if ($clients->hasPages())
                    <div class="flex gap-1.5">
                        @if ($prev = $clients->previousPageUrl())
                            <a href="{{ $prev }}" class="inline-flex h-11 items-center rounded-campo border border-linha px-3.5 font-semibold text-tinta hover:bg-areia">Anterior</a>
                        @endif
                        <span class="inline-flex h-11 items-center rounded-campo bg-tinta px-4 font-semibold text-white" aria-current="page">{{ $clients->currentPage() }}</span>
                        @if ($next = $clients->nextPageUrl())
                            <a href="{{ $next }}" class="inline-flex h-11 items-center rounded-campo border border-linha px-3.5 font-semibold text-tinta hover:bg-areia">Próxima</a>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-page>
