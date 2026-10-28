@php
    // Cada hora ocupa 80px de altura na grade (30 min = 40px)
    $pxPerMinute = 80 / 60;
    $gridStart = $day->copy()->setTime($startHour, 0);
    $gridHeight = ($endHour - $startHour) * 80;
    $money = fn ($value) => 'R$ '.number_format($value, 2, ',', '.');
    $nowTop = $day->isToday() && now()->hour >= $startHour && now()->hour < $endHour
        ? $gridStart->diffInMinutes(now()) * $pxPerMinute
        : null;

    // Celular: lista do dia, com filtro por profissional
    $filterId = $professionalFilter?->id;
    $mobileList = ($filterId ? $dayList->where('professional_id', $filterId) : $dayList)->values();
    $nowIndex = $day->isToday() ? $mobileList->search(fn ($a) => $a->starts_at->isAfter(now())) : false;
    $weekStart = $day->copy()->startOfWeek(\Carbon\CarbonInterface::MONDAY);
    $shortMoney = fn ($value) => 'R$ '.number_format($value, floor($value) == $value ? 0 : 2, ',', '.');
    $chip = 'flex min-h-10 shrink-0 items-center rounded-full border px-3.5 text-[13px] font-semibold';
@endphp

<x-app-layout title="Agenda" wide>
    {{-- Celular e tablet --}}
    <div class="flex flex-col gap-3 lg:hidden">
        <header class="-mx-4 -mt-5 flex flex-col gap-3.5 bg-tinta px-4 pb-3.5 pt-5 text-creme sm:-mx-6 sm:px-6">
            <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 flex-col">
                    <span class="truncate text-xs text-menu-suave">{{ Auth::user()->salon->name }}</span>
                    <h1 class="text-[22px] font-bold leading-tight">{{ str($day->translatedFormat('l'))->before('-')->ucfirst() }}, {{ $day->translatedFormat('j \d\e F') }}</h1>
                </div>
                <form method="GET" action="{{ route('appointments.index') }}" class="shrink-0">
                    @if ($filterId)
                        <input type="hidden" name="profissional" value="{{ $filterId }}">
                    @endif
                    <label class="relative flex h-11 w-11 cursor-pointer items-center justify-center rounded-2xl border border-menu-ativo">
                        <span class="sr-only">Escolher data</span>
                        <x-icon name="agenda" />
                        <input type="date" name="data" value="{{ $day->toDateString() }}" onchange="this.form.submit()" class="absolute inset-0 h-full w-full cursor-pointer opacity-0">
                    </label>
                </form>
            </div>
            <nav aria-label="Dias da semana" class="grid grid-cols-7 gap-1">
                @for ($i = 0; $i < 7; $i++)
                    @php
                        $weekDay = $weekStart->copy()->addDays($i);
                        $selected = $weekDay->isSameDay($day);
                    @endphp
                    <a href="{{ route('appointments.index', array_filter(['data' => $weekDay->toDateString(), 'profissional' => $filterId])) }}" @if ($selected) aria-current="date" @endif
                       class="flex h-14 flex-col items-center justify-center gap-0.5 rounded-2xl {{ $selected ? 'bg-cafe text-white' : 'text-menu-texto' }}">
                        <span class="text-[11px]">{{ ucfirst($weekDay->translatedFormat('D')) }}</span>
                        <span class="text-base font-bold">{{ $weekDay->day }}</span>
                    </a>
                @endfor
            </nav>
        </header>

        <x-flash />

        @if ($professionals->isEmpty())
            <div class="cartao p-6 text-suave">
                Cadastre pelo menos um <a href="{{ route('professionals.create') }}" class="font-semibold text-cafe underline">profissional</a> para usar a agenda.
            </div>
        @else
            <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:-mx-6 sm:px-6" aria-label="Filtrar por profissional">
                <a href="{{ route('appointments.index', ['data' => $day->toDateString()]) }}" @if (! $filterId) aria-current="true" @endif
                   class="{{ $chip }} {{ $filterId ? 'border-linha bg-cartao' : 'border-tinta bg-tinta text-white' }}">Todos · {{ $dayList->count() }}</a>
                @foreach ($professionals as $professional)
                    <a href="{{ route('appointments.index', ['data' => $day->toDateString(), 'profissional' => $professional->id]) }}" @if ($filterId === $professional->id) aria-current="true" @endif
                       class="{{ $chip }} {{ $filterId === $professional->id ? 'border-tinta bg-tinta text-white' : 'border-linha bg-cartao' }}">{{ strtok($professional->name, ' ') }} · {{ $appointments->get($professional->id, collect())->count() }}</a>
                @endforeach
            </div>

            {{-- Hoje, a lista começa no "Agora": os horários que já passaram ficam atrás de um botão --}}
            <div x-data="{ past: false }" class="flex flex-col gap-2">
                @if ($nowIndex)
                    <button type="button" x-show="! past" @click="past = true" class="flex min-h-11 items-center justify-center rounded-full border border-dashed border-linha-forte text-[13px] font-semibold text-suave">
                        Ver {{ $nowIndex }} {{ $nowIndex === 1 ? 'horário anterior' : 'horários anteriores' }}
                    </button>
                @endif
                @forelse ($mobileList as $i => $appointment)
                    @if ($nowIndex === $i)
                        <div class="flex items-center gap-2 text-xs font-bold text-cafe"><span>Agora · {{ now()->format('H:i') }}</span><span class="h-0.5 flex-1 bg-cafe"></span></div>
                    @endif
                    @php
                        $canceled = $appointment->status === \App\Enums\AppointmentStatus::Canceled;
                    @endphp
                    <div class="flex items-stretch gap-3" @if ($nowIndex && $i < $nowIndex) x-show="past" x-cloak @endif>
                        <span class="w-11 shrink-0 pt-3 text-sm font-bold">{{ $appointment->starts_at->format('H:i') }}</span>
                        <a href="{{ route('appointments.edit', $appointment) }}"
                           class="flex min-w-0 flex-1 flex-col gap-1 rounded-[18px] border border-linha bg-cartao px-3.5 py-2.5 shadow-cartao">
                            <span class="flex items-center justify-between gap-2">
                                <span class="truncate text-[15px] font-bold {{ $canceled ? 'text-suave line-through' : '' }}">{{ $appointment->client->name }}</span>
                                <span class="selo shrink-0 text-[11px] {{ $appointment->status->color() }}">{{ $appointment->status->label() }}</span>
                            </span>
                            <span class="truncate text-[13px] text-suave">{{ $appointment->service->name }} · {{ strtok($appointment->professional->name, ' ') }} · {{ $shortMoney($appointment->price) }}</span>
                        </a>
                    </div>
                @empty
                    <div class="cartao flex flex-col items-center gap-1 p-6 text-center text-sm text-suave">
                        Nenhum horário neste dia.
                        <a href="{{ route('appointments.create', array_filter(['data' => $day->toDateString(), 'profissional' => $filterId])) }}" class="inline-flex min-h-11 items-center font-semibold text-cafe">Agendar um horário</a>
                    </div>
                @endforelse
                @if ($day->isToday() && $nowIndex === false && $mobileList->isNotEmpty())
                    <div class="flex items-center gap-2 text-xs font-bold text-cafe"><span>Agora · {{ now()->format('H:i') }}</span><span class="h-0.5 flex-1 bg-cafe"></span></div>
                @endif
            </div>
        @endif

        <a href="{{ route('appointments.create', array_filter(['data' => $day->toDateString(), 'profissional' => $filterId])) }}"
           class="fixed bottom-[98px] right-4 z-30 flex h-14 w-14 items-center justify-center rounded-[22px] bg-cafe text-white shadow-[0_6px_16px_rgba(28,13,6,0.25)] hover:bg-cafe-escuro">
            <span class="sr-only">Novo agendamento</span>
            <x-icon name="mais" class="h-6 w-6" />
        </a>
    </div>

    {{-- Computador --}}
    <div class="hidden flex-col gap-5 lg:flex">
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
