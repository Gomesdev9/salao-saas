@php
    $token = $client->access_token;
    $weekdays = [1 => 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
    $reschedule = fn ($appointment) => route('booking.time', [
        'salon' => $salon,
        'servico' => $appointment->service_id,
        'profissional' => $appointment->professional_id,
        'data' => $appointment->starts_at->toDateString(),
        'remarcar' => $appointment->id,
    ]);
@endphp

<x-booking-page :salon="$salon" title="Meus horários">
    <div class="flex flex-col gap-1.5">
        <span class="text-xs font-bold uppercase tracking-[0.18em] text-cafe">Área da cliente</span>
        <h1 class="text-[32px] font-medium leading-tight">Olá, <em class="italic text-cafe">{{ strtok($client->name, ' ') }}</em></h1>
    </div>

    <x-flash />

    @if ($next)
        <section class="flex flex-col gap-4 rounded-cartao bg-tinta p-4 text-creme shadow-menu">
            <div class="flex items-center justify-between gap-3">
                <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-menu-suave">Próximo horário</span>
                <span class="selo {{ $next->status->color() }}">{{ $next->status->label() }}</span>
            </div>
            <div class="flex items-center gap-3.5">
                <span class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-[16px] bg-menu-ativo">
                    <span class="text-[10px] font-bold uppercase text-menu-suave">{{ $next->starts_at->translatedFormat('M') }}</span>
                    <span class="text-xl font-bold leading-none">{{ $next->starts_at->format('d') }}</span>
                </span>
                <div class="flex min-w-0 flex-col gap-0.5">
                    <span class="truncate text-lg font-bold">{{ $next->service->name }}</span>
                    <span class="text-sm text-menu-texto">{{ $weekdays[$next->starts_at->dayOfWeekIso] }} · {{ $next->starts_at->format('H:i') }} · {{ $next->service->duration_minutes }} min</span>
                    <span class="truncate text-[13px] text-menu-suave">Com {{ $next->professional->name }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <a href="{{ $reschedule($next) }}" class="inline-flex h-12 items-center justify-center rounded-full bg-pele text-[15px] font-semibold text-tinta hover:bg-white">Remarcar</a>
                <form method="POST" action="{{ route('booking.cancel', [$salon, $token, $next->id]) }}" onsubmit="return confirm('Cancelar este horário?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-full border border-menu-suave text-[15px] font-semibold hover:bg-menu-ativo">Cancelar</button>
                </form>
            </div>
        </section>
    @else
        <div class="cartao flex flex-col gap-1 p-5">
            <span class="text-base font-bold">Nenhum horário marcado</span>
            <span class="text-sm text-suave">Quando você agendar, o horário aparece aqui.</span>
        </div>
    @endif

    @foreach ($upcoming as $appointment)
        <article class="cartao flex items-center gap-3.5 p-4">
            <span class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-[16px] bg-pele text-cafe-escuro">
                <span class="text-[10px] font-bold uppercase">{{ $appointment->starts_at->translatedFormat('M') }}</span>
                <span class="text-xl font-bold leading-none">{{ $appointment->starts_at->format('d') }}</span>
            </span>
            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span class="truncate text-base font-bold">{{ $appointment->service->name }}</span>
                <span class="text-[13px] text-suave">{{ $weekdays[$appointment->starts_at->dayOfWeekIso] }} · {{ $appointment->starts_at->format('H:i') }} · {{ strtok($appointment->professional->name, ' ') }}</span>
                <span class="flex gap-4 pt-1 text-[13px] font-semibold">
                    <a href="{{ $reschedule($appointment) }}" class="inline-flex min-h-8 items-center text-cafe underline">Remarcar</a>
                    <form method="POST" action="{{ route('booking.cancel', [$salon, $token, $appointment->id]) }}" onsubmit="return confirm('Cancelar este horário?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex min-h-8 items-center text-perigo underline">Cancelar</button>
                    </form>
                </span>
            </div>
            <span class="selo self-start {{ $appointment->status->color() }}">{{ $appointment->status->label() }}</span>
        </article>
    @endforeach

    @if ($history->isNotEmpty())
        <section class="flex flex-col gap-3 pt-2">
            <h2 class="text-2xl font-medium">Histórico</h2>
            <ul class="cartao flex flex-col divide-y divide-linha-clara">
                @foreach ($history as $appointment)
                    <li class="flex items-center justify-between gap-3 px-4 py-3.5">
                        <span class="flex min-w-0 flex-col gap-1">
                            <span class="truncate text-[15px] font-bold">{{ $appointment->service->name }}</span>
                            <span class="flex flex-wrap items-center gap-2 text-[13px] text-suave">
                                {{ $appointment->starts_at->translatedFormat('j M Y') }}
                                <span class="selo !px-2 !py-0 text-[11px] {{ $appointment->status->color() }}">{{ $appointment->status->label() }}</span>
                            </span>
                        </span>
                        <a href="{{ route('booking.time', ['salon' => $salon, 'servico' => $appointment->service_id]) }}"
                           class="inline-flex h-10 shrink-0 items-center rounded-full border border-linha-forte px-4 text-[13px] font-semibold text-cafe-escuro hover:bg-areia">Agendar de novo</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($salon->whatsappUrl())
        <p class="text-center text-[13px] text-suave">Dúvidas? Fale com o salão pelo <a href="{{ $salon->whatsappUrl() }}" class="font-semibold text-cafe underline">WhatsApp</a>.</p>
    @endif

    <x-slot name="bar">
        <a href="{{ route('booking.service', $salon) }}" class="flex h-12 flex-1 items-center justify-center gap-2.5 text-[15px] font-semibold">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-creme text-tinta"><x-icon name="mais" class="h-4 w-4" /></span>
            Novo agendamento
        </a>
    </x-slot>
</x-booking-page>
