@php
    $client = $appointment->client;
@endphp

<x-page title="Editar agendamento" :back="route('appointments.index', ['data' => $appointment->starts_at->toDateString()])" :back-label="'Voltar para a agenda de '.$appointment->starts_at->format('d/m')">
    {{-- Situação do horário e troca rápida --}}
    <section aria-label="Situação do horário" class="cartao flex flex-wrap items-center gap-3 px-5 py-4">
        <div class="mr-auto flex flex-col gap-0.5">
            <span class="text-[13px] text-suave">
                {{ $appointment->starts_at->format('d/m') }} · {{ $appointment->starts_at->format('H:i') }}–{{ $appointment->ends_at->format('H:i') }} ·
                R$ {{ number_format($appointment->price, 2, ',', '.') }}
            </span>
            <span class="flex items-center gap-2 text-base font-bold">
                Situação: <span class="selo text-[13px] {{ $appointment->status->color() }}">{{ $appointment->status->label() }}</span>
            </span>
        </div>
        @foreach ($statuses as $status)
            @continue($status === $appointment->status)
            <form method="POST" action="{{ route('appointments.status', $appointment) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $status->value }}">
                <button type="submit" class="min-h-11 rounded-campo border px-3.5 text-[13px] font-semibold {{ $status->color() }}">Marcar {{ mb_strtolower($status->label()) }}</button>
            </form>
        @endforeach
        <x-input-error :messages="$errors->get('status')" class="w-full" />
    </section>

    <div class="flex flex-wrap items-start gap-5">
        <form method="POST" action="{{ route('appointments.update', $appointment) }}" class="cartao min-w-0 flex-[2_1_440px]">
            @csrf
            @method('PUT')
            @include('appointments._form')
        </form>

        <aside aria-label="Sobre a cliente" class="cartao flex min-w-0 flex-[1_1_260px] flex-col gap-3.5 p-5">
            <div class="flex items-center gap-3">
                <x-avatar :name="$client->name" size="h-11 w-11 text-[15px]" />
                <div class="flex flex-col">
                    <span class="text-base font-bold">{{ $client->name }}</span>
                    @if ($client->birthday)
                        <span class="text-[13px] text-suave">Aniversário {{ $client->birthday->format('d/m') }}</span>
                    @endif
                </div>
            </div>
            @if ($url = $client->whatsappUrl())
                <a href="{{ $url }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-campo border border-[#3E9A63] text-sm font-semibold text-[#1C5234] hover:bg-[#E7F4EC]">
                    <x-icon name="whatsapp" class="h-[18px] w-[18px]" />Chamar no WhatsApp
                </a>
            @endif
            @if ($client->notes)
                <div class="flex flex-col gap-1">
                    <span class="text-[13px] font-semibold text-suave">Observações da cliente</span>
                    <span class="rounded-campo bg-mel px-3 py-2.5 text-sm leading-5 text-mel-texto">{{ $client->notes }}</span>
                </div>
            @endif
            <a href="{{ route('clients.edit', $client) }}" class="text-sm font-semibold text-cafe hover:text-cafe-escuro">Ver cadastro completo</a>
        </aside>
    </div>

    <x-remove-form id="remover-agendamento" :action="route('appointments.destroy', $appointment)"
                   confirm="Remover este agendamento? Se o cliente só desmarcou, prefira marcar como cancelado." />
</x-page>
