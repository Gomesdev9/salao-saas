@php
    $money = fn ($value) => 'R$ '.number_format($value, 2, ',', '.');
    $duration = fn (int $minutes) => $minutes >= 60 && $minutes % 60 === 0
        ? ($minutes / 60).' h'
        : ($minutes > 60 ? intdiv($minutes, 60).' h '.($minutes % 60).' min' : "{$minutes} min");
@endphp

<x-page title="Serviços" subtitle="A duração define quanto tempo o horário ocupa na agenda.">
    <x-slot name="actions">
        <x-link-button :href="route('services.create')" icon="mais">Novo serviço</x-link-button>
    </x-slot>

    @if ($groups->isEmpty())
        <div class="cartao p-6 text-suave">Nenhum serviço cadastrado ainda.</div>
    @else
        @if ($categories->count() > 1)
            <nav class="flex flex-wrap gap-2" aria-label="Filtrar por categoria">
                @foreach ($categories->prepend(null) as $item)
                    @php
                        $on = $category === $item;
                    @endphp
                    <a href="{{ route('services.index', $item ? ['categoria' => $item] : []) }}" @if ($on) aria-current="page" @endif
                       class="inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold {{ $on ? 'border-tinta bg-tinta text-white' : 'border-linha bg-cartao hover:bg-areia' }}">{{ $item ?? 'Todos' }}</a>
                @endforeach
            </nav>
        @endif

        @foreach ($groups as $name => $services)
            <section class="cartao overflow-hidden">
                <div class="flex items-center justify-between border-b border-linha bg-areia px-5 py-3.5">
                    <h2 class="text-base font-bold">{{ $name }}</h2>
                    <span class="text-[13px] text-suave">{{ $services->count() }} {{ $services->count() === 1 ? 'serviço' : 'serviços' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] table-fixed border-collapse text-sm">
                        <colgroup>
                            <col>
                            <col class="w-[18%]">
                            <col class="w-[18%]">
                            <col class="w-[16%]">
                            <col class="w-[100px]">
                        </colgroup>
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-suave">
                                <th class="px-5 py-2.5 font-semibold">Serviço</th>
                                <th class="px-3 py-2.5 font-semibold">Duração</th>
                                <th class="px-3 py-2.5 font-semibold">Preço</th>
                                <th class="px-3 py-2.5 font-semibold">Situação</th>
                                <th class="px-5 py-2.5"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($services as $service)
                                <tr class="border-t border-linha-clara">
                                    <td class="px-5 py-3 font-semibold">{{ $service->name }}</td>
                                    <td class="px-3 py-3">{{ $duration($service->duration_minutes) }}</td>
                                    <td class="px-3 py-3 font-semibold">{{ $money($service->price) }}</td>
                                    <td class="px-3 py-3">
                                        <span @class(['selo border-transparent', 'bg-[#E7F4EC] text-[#1C5234]' => $service->active, 'bg-[#F2EDE7] text-suave' => ! $service->active])>
                                            {{ $service->active ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-1.5 text-right">
                                        <a href="{{ route('services.edit', $service) }}" class="inline-flex min-h-11 items-center font-semibold text-cafe hover:text-cafe-escuro">Editar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    @endif
</x-page>
