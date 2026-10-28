@use('App\Support\Money')

@php
    $mine = route('booking.mine', [$salon, $client->access_token]);
    $weekdays = [1 => 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
    $start = $appointment->starts_at;
@endphp

<x-booking-page :salon="$salon" title="Agendamento feito" :close="$mine">
    <div class="flex flex-col items-center gap-3 pt-2 text-center">
        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-pele text-cafe">
            <x-icon name="check" class="h-9 w-9" />
        </span>
        <span class="text-xs font-bold uppercase tracking-[0.18em] text-cafe">Agendamento feito</span>
        <h1 class="text-[32px] font-medium leading-tight">Tudo certo, <em class="italic text-cafe">{{ strtok($client->name, ' ') }}</em>!</h1>
        <p class="max-w-[320px] text-[15px] leading-relaxed text-suave">Seu horário está reservado. Se precisar mudar alguma coisa, é só remarcar em "Meus horários".</p>
    </div>

    <section class="overflow-hidden rounded-cartao border border-linha bg-cartao shadow-cartao">
        <div class="grid grid-cols-2 bg-tinta text-creme">
            <div class="flex flex-col gap-0.5 border-r border-menu-ativo px-4 py-3.5">
                <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-menu-suave">Data</span>
                <span class="text-lg font-bold">{{ $weekdays[$start->dayOfWeekIso] }}, {{ $start->translatedFormat('j \d\e M') }}</span>
            </div>
            <div class="flex flex-col gap-0.5 px-4 py-3.5">
                <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-menu-suave">Horário</span>
                <span class="text-lg font-bold">{{ $start->format('H:i') }}</span>
            </div>
        </div>
        <div class="flex flex-col gap-3 p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex flex-col gap-0.5">
                    <span class="text-base font-bold">{{ $appointment->service->name }}</span>
                    <span class="text-[13px] text-suave">{{ $appointment->service->duration_minutes }} min · {{ Money::format($appointment->price) }}</span>
                </div>
                <span class="selo {{ $appointment->status->color() }}">{{ $appointment->status->label() }}</span>
            </div>
            <ul class="flex flex-col gap-2 border-t border-linha-clara pt-3 text-sm">
                <li class="flex items-center gap-2.5"><x-icon name="usuario" class="h-[18px] w-[18px] text-cafe" />Com {{ $appointment->professional->name }}</li>
                @if ($salon->city)
                    <li class="flex items-center gap-2.5"><x-icon name="local" class="h-[18px] w-[18px] text-cafe" />{{ $salon->name }} · {{ $salon->city }}</li>
                @endif
            </ul>
        </div>
    </section>

    <div class="flex flex-col gap-2.5">
        <a href="{{ route('booking.calendar', [$salon, $client->access_token, $appointment->id]) }}"
           class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-cafe text-[15px] font-semibold text-white hover:bg-cafe-escuro">
            <x-icon name="agenda" class="h-[18px] w-[18px]" />Adicionar ao calendário
        </a>
        <a href="{{ $mine }}" class="inline-flex h-12 items-center justify-center rounded-full border border-linha-forte bg-cartao text-[15px] font-semibold hover:bg-areia">Ver meus horários</a>
    </div>

    <div class="flex flex-col items-center gap-2 text-center text-[13px] leading-relaxed text-suave" x-data="{ copied: false }">
        <p>Este aparelho já guarda os seus horários. Para ver em outro celular, guarde o link de "Meus horários".</p>
        <button type="button" class="inline-flex min-h-11 items-center gap-1.5 font-semibold text-cafe"
                @click="navigator.clipboard.writeText(@js($mine)); copied = true; setTimeout(() => copied = false, 2000)">
            <x-icon name="link" class="h-4 w-4" /><span x-text="copied ? 'Link copiado!' : 'Copiar meu link'">Copiar meu link</span>
        </button>
        @if ($salon->whatsappUrl())
            <p>Dúvidas? Fale com o salão pelo <a href="{{ $salon->whatsappUrl() }}" class="font-semibold text-cafe underline">WhatsApp</a>.</p>
        @endif
    </div>
</x-booking-page>
