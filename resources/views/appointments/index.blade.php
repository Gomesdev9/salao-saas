@php
    // Cada minuto vira 1,6px de altura na grade (15 min = 24px)
    $pxPerMinute = 1.6;
    $gridStart = $day->copy()->setTime($startHour, 0);
    $gridHeight = ($endHour - $startHour) * 60 * $pxPerMinute;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('appointments.index', ['data' => $day->copy()->subDay()->toDateString()]) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50" title="Dia anterior">&larr;</a>
                <form method="GET" action="{{ route('appointments.index') }}">
                    <input type="date" name="data" value="{{ $day->toDateString() }}" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm shadow-sm">
                </form>
                <a href="{{ route('appointments.index', ['data' => $day->copy()->addDay()->toDateString()]) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50" title="Próximo dia">&rarr;</a>
                @unless ($day->isToday())
                    <a href="{{ route('appointments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Hoje</a>
                @endunless
            </div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ ucfirst($day->locale('pt_BR')->translatedFormat('l, d \d\e F')) }}
            </h2>
            <x-link-button :href="route('appointments.create', ['data' => $day->toDateString()])">Novo agendamento</x-link-button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            @if ($professionals->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-600">
                    Cadastre pelo menos um <a href="{{ route('professionals.create') }}" class="text-indigo-600 underline">profissional</a> para usar a agenda.
                </div>
            @else
                <p class="mb-3 text-sm text-gray-600">
                    Previsto no dia: <strong>R$ {{ number_format($total, 2, ',', '.') }}</strong>.
                    Clique num horário vazio para agendar.
                </p>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <div class="flex min-w-max">
                        {{-- Coluna das horas --}}
                        <div class="w-14 shrink-0 border-r border-gray-100">
                            <div class="h-10 border-b border-gray-100"></div>
                            <div class="relative" style="height: {{ $gridHeight }}px">
                                @for ($h = $startHour; $h < $endHour; $h++)
                                    <div class="absolute right-2 -translate-y-2 text-xs text-gray-500" style="top: {{ ($h - $startHour) * 60 * $pxPerMinute }}px">
                                        {{ sprintf('%02d:00', $h) }}
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- Uma coluna por profissional --}}
                        @foreach ($professionals as $professional)
                            <div class="w-48 shrink-0 border-r border-gray-100 last:border-r-0">
                                <div class="flex h-10 items-center justify-center border-b border-gray-100 px-2 text-sm font-semibold text-gray-800 truncate">
                                    {{ $professional->name }}
                                </div>
                                <div class="relative" style="height: {{ $gridHeight }}px">
                                    {{-- Linhas de fundo e links para agendar em horário vazio (a cada 30 min) --}}
                                    @for ($m = 0; $m < ($endHour - $startHour) * 60; $m += 30)
                                        @php
                                            $slotTime = $gridStart->copy()->addMinutes($m);
                                        @endphp
                                        <a href="{{ route('appointments.create', ['data' => $day->toDateString(), 'hora' => $slotTime->format('H:i'), 'profissional' => $professional->id]) }}"
                                           class="absolute inset-x-0 block border-t {{ $m % 60 === 0 ? 'border-gray-200' : 'border-gray-100 border-dashed' }} hover:bg-indigo-50/60"
                                           style="top: {{ $m * $pxPerMinute }}px; height: {{ 30 * $pxPerMinute }}px"
                                           title="Agendar às {{ $slotTime->format('H:i') }}"></a>
                                    @endfor

                                    @foreach ($appointments->get($professional->id, []) as $appointment)
                                        @php
                                            $top = $gridStart->diffInMinutes($appointment->starts_at) * $pxPerMinute;
                                            $height = max(24, $appointment->starts_at->diffInMinutes($appointment->ends_at) * $pxPerMinute - 2);
                                        @endphp
                                        <a href="{{ route('appointments.edit', $appointment) }}"
                                           class="absolute inset-x-1 overflow-hidden rounded-md border-l-4 border px-2 py-1 text-xs shadow-sm hover:shadow {{ $appointment->status->color() }}"
                                           style="top: {{ $top }}px; height: {{ $height }}px">
                                            <div class="font-semibold truncate">{{ $appointment->starts_at->format('H:i') }} {{ $appointment->client->name }}</div>
                                            <div class="truncate">{{ $appointment->service->name }} · {{ $appointment->status->label() }}</div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
