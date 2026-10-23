<x-page :title="$greeting.', '.$firstName" :eyebrow="Auth::user()->salon->name" :subtitle="ucfirst(today()->translatedFormat('l, d \d\e F'))">
    <x-slot name="actions">
        <x-link-button variant="secondary" :href="route('clients.create')">Novo cliente</x-link-button>
        <x-link-button :href="route('appointments.create')" icon="mais">Novo agendamento</x-link-button>
    </x-slot>

    @if (collect($firstSteps)->contains('done', false))
        <section class="cartao flex flex-col gap-4 px-5 py-5 sm:px-6">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-bold">Primeiros passos</h2>
                <p class="text-sm text-suave">Deixe o sistema pronto para a sua empresa. Este quadro some quando tudo estiver feito.</p>
            </div>
            <ol class="grid gap-3 md:grid-cols-3">
                @foreach ($firstSteps as $i => $step)
                    <li>
                        <a href="{{ route($step['route']) }}"
                           class="flex min-h-14 items-center gap-3 rounded-campo border px-4 py-3 text-sm font-semibold transition {{ $step['done'] ? 'border-[#3E9A63]/40 bg-[#E7F4EC] text-[#1C5234]' : 'border-linha bg-creme hover:border-caramelo' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $step['done'] ? 'bg-[#3E9A63] text-white' : 'bg-pele text-cafe-escuro' }}">
                                @if ($step['done'])
                                    <x-icon name="check" class="h-4 w-4" /><span class="sr-only">Feito:</span>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </span>
                            {{ $step['label'] }}
                        </a>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    <div class="grid gap-3 sm:grid-cols-3">
        <x-stat highlight :href="route('appointments.index')" label="Atendimentos hoje" :value="$todayCount"
                :hint="$doneCount.' já atendidos · '.($todayCount - $doneCount).' a seguir'" />
        <x-stat :href="route('appointments.index')" label="Previsto hoje" :value="'R$ '.number_format($expected, 2, ',', '.')"
                hint="Soma dos horários não cancelados" />
        <x-stat :href="route('appointments.index')" label="A confirmar" :value="$toConfirm" hint="Horários ainda como “Agendado”" />
    </div>

    <div class="flex flex-wrap items-start gap-5">
        <section class="cartao min-w-0 flex-[2_1_420px]">
            <div class="flex items-center justify-between border-b border-linha px-5 py-4">
                <h2 class="text-[17px] font-bold">Próximos horários</h2>
                <a href="{{ route('appointments.index') }}" class="text-sm font-semibold text-cafe hover:text-cafe-escuro">Ver agenda</a>
            </div>
            @forelse ($next as $appointment)
                <a href="{{ route('appointments.edit', $appointment) }}" class="flex items-center gap-4 border-b border-linha-clara px-5 py-3.5 last:border-b-0 hover:bg-areia/60">
                    <span class="w-[52px] text-[15px] font-bold">{{ $appointment->starts_at->format('H:i') }}</span>
                    <span class="flex min-w-0 flex-1 flex-col">
                        <span class="truncate text-sm font-semibold">{{ $appointment->client->name }}</span>
                        <span class="truncate text-[13px] text-suave">{{ $appointment->service->name }} com {{ strtok($appointment->professional->name, ' ') }}</span>
                    </span>
                    <span class="selo {{ $appointment->status->color() }}">{{ $appointment->status->label() }}</span>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-suave">Nenhum horário pela frente hoje.</p>
            @endforelse
        </section>

        <div class="flex min-w-0 flex-[1_1_280px] flex-col gap-5">
            <section class="cartao flex flex-col gap-3.5 px-5 py-4">
                <h2 class="text-[17px] font-bold">Aniversariantes da semana</h2>
                @forelse ($birthdays as $client)
                    <a href="{{ route('clients.edit', $client) }}" class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-mel text-xs font-bold text-mel-texto">{{ $client->nextBirthday()->format('d/m') }}</span>
                        <span class="flex min-w-0 flex-col">
                            <span class="truncate text-sm font-semibold">{{ $client->name }}</span>
                            <span class="text-[13px] text-suave">{{ $client->whatsapp ?: 'Sem WhatsApp' }}</span>
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-suave">Nenhum aniversário nos próximos 7 dias.</p>
                @endforelse
            </section>

            <section class="cartao flex flex-col px-5 py-4">
                <h2 class="mb-1.5 text-[17px] font-bold">Cadastros</h2>
                @foreach ([['professionals.index', 'Profissionais ativos', $professionalsCount], ['services.index', 'Serviços ativos', $servicesCount], ['clients.index', 'Clientes', $clientsCount]] as [$route, $label, $count])
                    <a href="{{ route($route) }}" class="flex min-h-11 items-center justify-between border-b border-linha-clara text-sm last:border-b-0 hover:text-cafe">
                        <span>{{ $label }}</span><strong>{{ $count }}</strong>
                    </a>
                @endforeach
            </section>
        </div>
    </div>
</x-page>
