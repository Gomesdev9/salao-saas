@use('App\Support\Money')

@php
    // A categoria com mais procedimentos aparece primeiro
    $groups = $services->groupBy(fn ($service) => $service->category ?: 'Outros')->sortByDesc(fn ($items) => $items->count());
@endphp

<x-booking-page :salon="$salon" title="Agendar" :step="1" :account="$client ? route('booking.mine', [$salon, $client->access_token]) : null">
    <x-slot name="heading">Qual procedimento você quer <em>agendar</em>?</x-slot>

    @if ($services->isEmpty())
        <div class="cartao flex flex-col gap-3 p-5 text-[15px] leading-relaxed text-suave">
            Ainda não há procedimentos para agendar por aqui.
            @if ($salon->whatsappUrl())
                <a href="{{ $salon->whatsappUrl() }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-cafe font-semibold text-white">
                    <x-icon name="whatsapp" class="h-5 w-5" />Falar com o salão no WhatsApp
                </a>
            @endif
        </div>
    @else
        <form id="escolha" method="GET" action="{{ route('booking.time', $salon) }}" class="flex flex-col gap-4"
              x-data="{ tab: @js($groups->keys()->first()), selected: @js((string) $selected?->id) }">
            @if ($reschedule)
                <input type="hidden" name="remarcar" value="{{ $reschedule }}">
            @endif

            @if ($groups->count() > 1)
                <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1" role="tablist" aria-label="Categorias">
                    @foreach ($groups->keys() as $category)
                        <button type="button" role="tab" @click="tab = @js($category)" :aria-selected="(tab === @js($category)).toString()"
                                :class="tab === @js($category) ? 'border-tinta bg-tinta text-white' : 'border-linha bg-cartao'"
                                class="flex min-h-11 shrink-0 items-center rounded-full border px-5 text-sm font-semibold">{{ $category }}</button>
                    @endforeach
                </div>
            @endif

            @foreach ($groups as $category => $items)
                <fieldset class="flex flex-col gap-3" x-show="tab === @js($category)">
                    <legend class="sr-only">{{ $category }}</legend>
                    @foreach ($items as $service)
                        <label class="flex cursor-pointer items-center gap-3 rounded-[18px] border border-linha bg-cartao px-4 py-3.5 shadow-cartao transition has-[:checked]:border-cafe has-[:checked]:ring-1 has-[:checked]:ring-cafe has-[:focus-visible]:ring-2">
                            <input type="radio" name="servico" value="{{ $service->id }}" required class="peer sr-only"
                                   x-model="selected" @change="$dispatch('resumo', { text: @js($service->name) })" @checked($selected?->is($service))>
                            <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <span class="text-base font-bold">{{ $service->name }}</span>
                                <span class="text-[13px] text-suave">{{ $service->duration_minutes }} min · {{ Money::format($service->price) }}</span>
                            </span>
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-linha-forte text-transparent peer-checked:border-cafe peer-checked:bg-cafe peer-checked:text-white">
                                <x-icon name="check" class="h-4 w-4" />
                            </span>
                        </label>
                    @endforeach
                </fieldset>
            @endforeach
        </form>

        @if ($salon->whatsappUrl())
            <a href="{{ $salon->whatsappUrl() }}" class="self-center text-sm font-semibold text-cafe underline">Não sabe qual escolher? Fale com a gente</a>
        @endif
    @endif

    @if ($services->isNotEmpty())
        <x-slot name="bar">
            <span class="flex min-w-0 flex-col" x-data="{ text: @js($selected?->name ?? 'Escolha na lista') }" @resumo.window="text = $event.detail.text">
                <span class="text-xs text-menu-suave">Procedimento</span>
                <span class="truncate text-sm font-bold" x-text="text">{{ $selected?->name ?? 'Escolha na lista' }}</span>
            </span>
            <button type="submit" form="escolha" class="inline-flex h-12 shrink-0 items-center gap-2 rounded-full bg-pele px-5 text-[15px] font-semibold text-tinta hover:bg-white">
                Continuar<x-icon name="seta" class="h-[18px] w-[18px]" />
            </button>
        </x-slot>
    @endif
</x-booking-page>
