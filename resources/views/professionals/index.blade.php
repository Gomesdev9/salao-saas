<x-page title="Profissionais" subtitle="Quem atende no salão. Só os ativos aparecem na agenda.">
    <x-slot name="actions">
        <x-link-button :href="route('professionals.create')" icon="mais">Novo profissional</x-link-button>
    </x-slot>

    <div class="flex gap-2">
        @foreach ([false => "Ativos ({$activeCount})", true => "Inativos ({$inactiveCount})"] as $inactive => $label)
            @php($on = $showInactive === (bool) $inactive)
            <a href="{{ route('professionals.index', $inactive ? ['inativos' => 1] : []) }}" @if ($on) aria-current="page" @endif
               class="inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold {{ $on ? 'border-tinta bg-tinta text-white' : 'border-linha bg-cartao hover:bg-areia' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if ($professionals->isEmpty())
        <div class="cartao p-6 text-suave">{{ $showInactive ? 'Nenhum profissional inativo.' : 'Nenhum profissional cadastrado ainda.' }}</div>
    @else
        <div class="grid gap-4 [grid-template-columns:repeat(auto-fill,minmax(280px,1fr))]">
            @foreach ($professionals as $professional)
                <article class="cartao flex flex-col gap-3.5 p-5">
                    <div class="flex items-center gap-3">
                        <x-avatar :name="$professional->name" size="h-12 w-12 text-base" @class(['!bg-[#F2EDE7] !text-suave' => ! $professional->active]) />
                        <div class="flex min-w-0 flex-1 flex-col">
                            <h2 class="truncate text-base font-bold">{{ $professional->name }}</h2>
                            <span class="text-[13px] text-suave">{{ $professional->phone ?: 'Sem telefone' }}</span>
                        </div>
                        <span @class(['selo border-transparent', 'bg-[#E7F4EC] text-[#1C5234]' => $professional->active, 'bg-[#F2EDE7] text-suave' => ! $professional->active])>
                            {{ $professional->active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                    <div class="flex gap-6">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs text-suave">Comissão</span>
                            <span class="text-lg font-bold">{{ rtrim(rtrim(number_format($professional->commission_percent, 2, ',', ''), '0'), ',') }}%</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs text-suave">Horários hoje</span>
                            <span class="text-lg font-bold">{{ $professional->active ? $professional->today_count : '—' }}</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse ($professional->services as $service)
                            <span class="rounded-full border border-linha bg-creme px-2.5 py-1 text-xs font-medium">{{ $service->name }}</span>
                        @empty
                            <span class="text-xs text-suave">Nenhum serviço vinculado</span>
                        @endforelse
                    </div>
                    <div class="mt-auto flex gap-2 border-t border-linha-clara pt-3">
                        <a href="{{ route('professionals.edit', $professional) }}" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-campo border border-linha text-sm font-semibold hover:bg-areia">Editar</a>
                        <a href="{{ route('appointments.index') }}" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-campo border border-linha text-sm font-semibold hover:bg-areia">Ver agenda</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-page>
