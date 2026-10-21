@php
    // Cada hora ocupa 80px de altura na grade (30 min = 40px)
    $pxPerMinute = 80 / 60;
    $gridStart = $day->copy()->setTime($startHour, 0);
    $gridHeight = ($endHour - $startHour) * 80;
    $money = fn ($value) => 'R$ '.number_format($value, 2, ',', '.');
    $nowTop = $day->isToday() && now()->hour >= $startHour && now()->hour < $endHour
        ? $gridStart->diffInMinutes(now()) * $pxPerMinute
        : null;
@endphp

<x-app-layout title="Agenda" wide>
    <div class="flex flex-col gap-5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-semibold uppercase tracking-wider text-cafe">Agenda</span>
                <h1 class="text-[28px] font-semibold leading-tight sm:text-[32px]">{{ ucfirst($day->translatedFormat('l, d \d\e F')) }}</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex h-11 items-center rounded-campo border border-linha bg-cartao">
                    <a href="{{ route('appointments.index', ['data' => $day->copy()->subDay()->toDateString()]) }}" class="flex h-11 w-11 items-center justify-center hover:text-cafe">
                        <span class="sr-only">Dia anterior</span><x-icon name="chevron-left" class="h-[18px] w-[18px]" />
                    </a>
                    <a href="{{ route('appointments.index') }}" class="flex h-11 items-center border-x border-linha px-3.5 text-sm font-semibold hover:text-cafe">Hoje</a>
                    <a href="{{ route('appointments.index', ['data' => $day->copy()->addDay()->toDateString()]) }}" class="flex h-11 w-11 items-center justify-center hover:text-cafe">
                        <span class="sr-only">Próximo dia</span><x-icon name="chevron-right" class="h-[18px] w-[18px]" />
                    </a>
                </div>
                <form method="GET" action="{{ route('appointments.index') }}">
                    <label for="data" class="sr-only">Escolher data</label>
                    <input id="data" type="date" name="data" value="{{ $day->toDateString() }}" onchange="this.form.submit()" class="campo w-auto text-sm">
                </form>
                <x-link-button :href="route('appointments.create', ['data' => $day->toDateString()])" icon="mais">Novo agendamento</x-link-button>
            </div>
        </div>

        <x-flash />

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-stat label="Previsto no dia" :value="$money($total)" />
            <x-stat label="Atendimentos" :value="$count" />
            <x-stat label="Já atendidos" :value="$doneCount" />
            <x-stat label="Faltas e cancelamentos" :value="$missedCount" />
        </div>

        @if ($professionals->isEmpty())
            <div class="cartao p-6 text-suave">
                Cadastre pelo menos um <a href="{{ route('professionals.create') }}" class="font-semibold text-cafe underline">profissional</a> para usar a agenda.
            </div>
        @else
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap gap-2" aria-label="Legenda das situações">
                    @foreach (\App\Enums\AppointmentStatus::cases() as $status)
                        <span class="selo {{ $status->color() }}">{{ $status->label() }}</span>
                    @endforeach
                </div>
                <span class="text-[13px] text-suave">Clique num horário vazio para agendar.</span>
            </div>

            <div class="cartao overflow-x-auto">
                <div class="flex min-w-max">
                    {{-- Coluna das horas --}}
                    <div class="w-16 shrink-0 border-r border-linha">
                        <div class="h-16 border-b border-linha"></div>
                        <div class="relative" style="height: {{ $gridHeight }}px">
                            @for ($h = $startHour; $h < $endHour; $h++)
                                <span class="absolute right-2.5 text-xs text-suave" style="top: {{ ($h - $startHour) * 80 + 4 }}px">{{ sprintf('%02d:00', $h) }}</span>
                            @endfor
                        </div>
                    </div>

                    {{-- Uma coluna por profissional --}}
                    <div class="relative flex flex-1">
                        @foreach ($professionals as $professional)
                            @php
                                $list = $appointments->get($professional->id, collect());
                                $area = $professional->area();
                            @endphp
                            <div class="w-[200px] flex-1 border-r border-linha last:border-r-0">
                                <div class="flex h-16 items-center gap-2.5 border-b border-linha px-3.5">
                                    <x-avatar :name="$professional->name" />
                                    <div class="flex min-w-0 flex-col">
                                        <span class="truncate text-sm font-semibold">{{ $professional->name }}</span>
                                        <span class="truncate text-xs text-suave">{{ $area ? "{$area} · " : '' }}{{ $list->count() }} {{ $list->count() === 1 ? 'horário' : 'horários' }}</span>
                                    </div>
                                </div>
                                <div class="relative" style="height: {{ $gridHeight }}px">
                                    {{-- Linhas de fundo e links para agendar em horário vazio (a cada 30 min) --}}
                                    @for ($m = 0; $m < ($endHour - $startHour) * 60; $m += 30)
                                        @php
                                            $slotTime = $gridStart->copy()->addMinutes($m);
                                        @endphp
                                        <a href="{{ route('appointments.create', ['data' => $day->toDateString(), 'hora' => $slotTime->format('H:i'), 'profissional' => $professional->id]) }}"
                                           class="group absolute inset-x-0 block border-t {{ $m % 60 === 0 ? 'border-linha' : 'border-linha-clara' }}"
                                           style="top: {{ $m * $pxPerMinute }}px; height: 40px"
                                           title="Agendar às {{ $slotTime->format('H:i') }}">
                                            <span class="absolute inset-x-1.5 inset-y-1 hidden items-center gap-1.5 rounded-[10px] border border-dashed border-caramelo bg-[#FDF3EC] px-2 text-xs font-semibold text-cafe group-hover:flex">
                                                <x-icon name="mais" class="h-3.5 w-3.5" />Agendar às {{ $slotTime->format('H:i') }}
                                            </span>
                                        </a>
                                    @endfor

                                    @foreach ($list as $appointment)
                                        @php
                                            $top = $gridStart->diffInMinutes($appointment->starts_at) * $pxPerMinute;
                                            $height = max(28, $appointment->starts_at->diffInMinutes($appointment->ends_at) * $pxPerMinute - 4);
                                        @endphp
                                        <a href="{{ route('appointments.edit', $appointment) }}"
                                           class="absolute inset-x-1.5 overflow-hidden rounded-[10px] border px-2 py-1 text-xs leading-[15px] shadow-sm transition hover:shadow-md {{ $appointment->status->color() }} {{ $appointment->status === \App\Enums\AppointmentStatus::Canceled ? 'line-through' : '' }}"
                                           style="top: {{ $top }}px; height: {{ $height }}px">
                                            <span class="block truncate font-bold">{{ $appointment->starts_at->format('H:i') }} {{ $appointment->client->name }}</span>
                                            <span class="block truncate">{{ $appointment->service->name }} · {{ $appointment->status->label() }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        {{-- Linha da hora atual --}}
                        @if ($nowTop !== null)
                            <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 h-0.5 bg-cafe" style="top: {{ 64 + $nowTop }}px"></div>
                            <span class="pointer-events-none absolute left-1.5 rounded-lg bg-cafe px-1.5 py-px text-[11px] font-bold text-white" style="top: {{ 64 + $nowTop - 9 }}px">{{ now()->format('H:i') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
