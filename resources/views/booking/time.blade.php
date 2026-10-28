@use('App\Support\Money')

@php
    $base = array_filter(['salon' => $salon, 'servico' => $service->id, 'remarcar' => $reschedule]);
    $weekdays = [1 => 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
    $dayLabel = $weekdays[$day->dayOfWeekIso].', '.$day->translatedFormat('j \d\e M');
    $periods = collect($times)->groupBy(fn ($id, $time) => $time < '12:00' ? 'Manhã' : ($time < '18:00' ? 'Tarde' : 'Noite'), preserveKeys: true);
    $hasFree = collect($times)->filter()->isNotEmpty();
    $chip = 'flex min-h-11 shrink-0 items-center gap-2 rounded-full border pl-1.5 pr-4 text-sm font-semibold';
@endphp

<x-booking-page :salon="$salon" title="Dia e horário" :step="2" :back="route('booking.service', $base)"
                :account="$client ? route('booking.mine', [$salon, $client->access_token]) : null">
    <x-slot name="heading">Escolha o dia e o <em>horário</em></x-slot>

    <div class="flex items-center justify-between gap-3 rounded-[18px] bg-pele px-4 py-3.5">
        <div class="flex min-w-0 flex-col gap-0.5">
            <span class="truncate text-base font-bold">{{ $service->name }}</span>
            <span class="text-[13px] text-suave">{{ $service->duration_minutes }} min · {{ Money::format($service->price) }}</span>
        </div>
        <a href="{{ route('booking.service', $base) }}" class="inline-flex h-10 shrink-0 items-center rounded-full bg-cartao px-4 text-sm font-semibold text-cafe-escuro shadow-cartao">Trocar</a>
    </div>

    <section class="flex flex-col gap-2.5">
        <h2 class="font-sans text-[15px] font-semibold">Com quem?</h2>
        <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1">
            <a href="{{ route('booking.time', [...$base, 'data' => $day->toDateString()]) }}" @if (! $professional) aria-current="true" @endif
               class="{{ $chip }} {{ $professional ? 'border-linha bg-cartao' : 'border-tinta bg-tinta text-white' }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $professional ? 'bg-pele text-cafe-escuro' : 'bg-menu-ativo text-[#D9A57F]' }}"><x-icon name="estrela" class="h-4 w-4" /></span>
                Sem preferência
            </a>
            @foreach ($professionals as $item)
                @php
                    $on = $professional?->is($item);
                @endphp
                <a href="{{ route('booking.time', [...$base, 'profissional' => $item->id, 'data' => $day->toDateString()]) }}" @if ($on) aria-current="true" @endif
                   class="{{ $chip }} {{ $on ? 'border-tinta bg-tinta text-white' : 'border-linha bg-cartao' }}">
                    <x-avatar :name="$item->name" size="h-8 w-8 text-xs" @class(['!bg-menu-ativo !text-creme' => $on]) />
                    {{ strtok($item->name, ' ') }}
                </a>
            @endforeach
        </div>
    </section>

    <section class="flex flex-col gap-2.5">
        <div class="flex items-baseline justify-between gap-3">
            <h2 class="font-sans text-[15px] font-semibold">Dia</h2>
            <span class="text-[13px] text-suave">{{ ucfirst($day->translatedFormat('F \d\e Y')) }}</span>
        </div>
        <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1">
            @foreach ($days as $option)
                @php
                    $selected = $option->isSameDay($day);
                    $open = (bool) $salon->hoursOn($option);
                    $cell = 'flex h-[60px] w-[50px] shrink-0 flex-col items-center justify-center rounded-[16px] border';
                @endphp
                @if ($open)
                    <a href="{{ route('booking.time', array_filter([...$base, 'profissional' => $professional?->id, 'data' => $option->toDateString()])) }}" @if ($selected) aria-current="date" @endif
                       class="{{ $cell }} {{ $selected ? 'border-cafe bg-cafe text-white' : 'border-linha bg-cartao hover:border-caramelo' }}">
                        <span class="text-[11px] font-medium {{ $selected ? 'text-pele' : 'text-suave' }}">{{ $weekdays[$option->dayOfWeekIso] }}</span>
                        <span class="text-lg font-bold">{{ $option->day }}</span>
                    </a>
                @else
                    <span class="{{ $cell }} border-transparent bg-areia text-suave/60" title="Fechado">
                        <span class="text-[11px] font-medium">{{ $weekdays[$option->dayOfWeekIso] }}</span>
                        <span class="text-lg font-bold">{{ $option->day }}</span>
                    </span>
                @endif
            @endforeach
        </div>
        <span class="text-xs text-suave">{{ $salon->openingHoursSummary() }}</span>
    </section>

    <form id="horario" method="GET" action="{{ route('booking.details', $salon) }}" class="flex flex-col gap-3"
          x-data @change="$dispatch('resumo', { text: @js($dayLabel.' · ') + $event.target.value })">
        <input type="hidden" name="servico" value="{{ $service->id }}">
        <input type="hidden" name="data" value="{{ $day->toDateString() }}">
        @if ($professional)
            <input type="hidden" name="profissional" value="{{ $professional->id }}">
        @endif
        @if ($reschedule)
            <input type="hidden" name="remarcar" value="{{ $reschedule }}">
        @endif

        <h2 class="font-sans text-[15px] font-semibold">Horários livres</h2>

        @error('hora')
            <div role="alert" class="rounded-campo bg-mel px-4 py-3 text-sm font-medium text-mel-texto">{{ $message }}</div>
        @enderror

        @if (! $salon->hoursOn($day))
            <p class="cartao p-4 text-sm text-suave">O salão não abre neste dia. Escolha outro dia acima.</p>
        @elseif (! $hasFree)
            <p class="cartao p-4 text-sm text-suave">Não há horário livre neste dia{{ $professional ? ' com '.strtok($professional->name, ' ') : '' }}. Escolha outro dia{{ $professional ? ' ou "Sem preferência"' : '' }}.</p>
        @endif

        @foreach ($periods as $period => $slots)
            <fieldset class="flex flex-col gap-2">
                <legend class="pb-2 text-xs font-bold uppercase tracking-[0.14em] text-suave">{{ $period }}</legend>
                <div class="grid grid-cols-4 gap-2">
                    @foreach ($slots as $time => $professionalId)
                        @if ($professionalId)
                            <label class="flex h-12 cursor-pointer items-center justify-center rounded-campo border border-linha bg-cartao text-[15px] font-semibold transition hover:border-caramelo has-[:checked]:border-cafe has-[:checked]:bg-cafe has-[:checked]:text-white has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-caramelo">
                                <input type="radio" name="hora" value="{{ $time }}" required class="sr-only" @checked($chosenTime === $time)>
                                {{ $time }}
                            </label>
                        @else
                            <span class="flex h-12 items-center justify-center rounded-campo bg-areia text-[15px] text-suave/70 line-through" title="Ocupado">
                                <span class="sr-only">Ocupado: </span>{{ $time }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </fieldset>
        @endforeach
    </form>

    @if ($hasFree)
        <x-slot name="bar">
            <span class="flex min-w-0 flex-col" x-data="{ text: @js($chosenTime ? $dayLabel.' · '.$chosenTime : $dayLabel) }" @resumo.window="text = $event.detail.text">
                <span class="truncate text-xs text-menu-suave">{{ $professional?->name ?? 'Qualquer profissional' }}</span>
                <span class="truncate text-sm font-bold" x-text="text">{{ $dayLabel }}</span>
            </span>
            <button type="submit" form="horario" class="inline-flex h-12 shrink-0 items-center gap-2 rounded-full bg-pele px-5 text-[15px] font-semibold text-tinta hover:bg-white">
                Continuar<x-icon name="seta" class="h-[18px] w-[18px]" />
            </button>
        </x-slot>
    @endif
</x-booking-page>
