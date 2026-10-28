@use('App\Support\Money')

@php
    $params = array_filter([
        'salon' => $salon,
        'servico' => $service->id,
        'profissional' => $professional?->id,
        'data' => $start->toDateString(),
        'hora' => $start->format('H:i'),
        'remarcar' => $reschedule,
    ]);
@endphp

<x-booking-page :salon="$salon" title="Seus dados" :step="3" :back="route('booking.time', $params)"
                :account="$client ? route('booking.mine', [$salon, $client->access_token]) : null">
    <x-slot name="heading">Quase lá! Confira seus <em>dados</em></x-slot>

    <section class="cartao flex flex-col gap-3 p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 flex-col gap-0.5">
                <span class="text-base font-bold">{{ $service->name }}</span>
                <span class="text-[13px] text-suave">{{ $service->duration_minutes }} min · {{ Money::format($service->price) }}</span>
            </div>
            <a href="{{ route('booking.time', $params) }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-cafe underline">Alterar</a>
        </div>
        <ul class="flex flex-col gap-2 border-t border-linha-clara pt-3 text-sm">
            <li class="flex items-center gap-2.5">
                <x-icon name="agenda" class="h-[18px] w-[18px] text-cafe" />
                <span><strong>{{ ucfirst($start->translatedFormat('l, j \d\e F')) }}</strong> às {{ $start->format('H:i') }}</span>
            </li>
            <li class="flex items-center gap-2.5">
                <x-icon name="usuario" class="h-[18px] w-[18px] text-cafe" />
                Com {{ $assigned->name }}
            </li>
            @if ($salon->city)
                <li class="flex items-center gap-2.5">
                    <x-icon name="local" class="h-[18px] w-[18px] text-cafe" />
                    {{ $salon->name }} · {{ $salon->city }}
                </li>
            @endif
        </ul>
    </section>

    <form id="dados" method="POST" action="{{ route('booking.store', $salon) }}" class="flex flex-col gap-4">
        @csrf
        <input type="hidden" name="servico" value="{{ $service->id }}">
        <input type="hidden" name="data" value="{{ $start->toDateString() }}">
        <input type="hidden" name="hora" value="{{ $start->format('H:i') }}">
        @if ($professional)
            <input type="hidden" name="profissional" value="{{ $professional->id }}">
        @endif
        @if ($reschedule)
            <input type="hidden" name="remarcar" value="{{ $reschedule }}">
        @endif

        <div>
            <x-input-label for="name" value="Nome completo" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $client?->name)" required autocomplete="name" class="h-12" />
            <x-input-error :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="whatsapp" value="WhatsApp" />
            <x-text-input id="whatsapp" name="whatsapp" type="tel" inputmode="tel" :value="old('whatsapp', $client?->whatsapp)" required autocomplete="tel" placeholder="(11) 98765-4321" class="h-12" />
            <x-input-error :messages="$errors->get('whatsapp')" />
            <p class="mt-1.5 text-xs text-suave">O salão usa o seu WhatsApp só para falar sobre os seus horários.</p>
        </div>
        <div>
            <x-input-label for="notes">Algo que a gente precisa saber? <span class="font-normal text-suave">(opcional)</span></x-input-label>
            <x-textarea-input id="notes" name="notes" rows="3" placeholder="Alergias, gestação, procedimentos recentes...">{{ old('notes') }}</x-textarea-input>
            <x-input-error :messages="$errors->get('notes')" />
        </div>

        @if ($reschedule && $client)
            <p class="rounded-campo bg-mel px-4 py-3 text-sm text-mel-texto">Ao confirmar, o horário antigo é cancelado e fica valendo este.</p>
        @endif
    </form>

    <x-slot name="bar">
        <span class="flex min-w-0 flex-col">
            <span class="text-xs text-menu-suave">Total</span>
            <span class="text-base font-bold">{{ Money::format($service->price) }}</span>
        </span>
        <button type="submit" form="dados" class="inline-flex h-12 shrink-0 items-center rounded-full bg-pele px-5 text-[15px] font-semibold text-tinta hover:bg-white">
            Confirmar agendamento
        </button>
    </x-slot>
</x-booking-page>
