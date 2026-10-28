@props(['plan', 'current' => false])

@php
    $highlight = $plan->isHighlighted();
@endphp

{{-- Cartão de um plano (página de divulgação e "Meu plano"). O botão vem no slot. --}}
<article {{ $attributes->class([
    'relative flex flex-col gap-5 rounded-cartao p-6 sm:p-7',
    'bg-tinta text-creme shadow-menu' => $highlight,
    'cartao' => ! $highlight,
    'ring-2 ring-cafe ring-offset-2 ring-offset-creme' => $current,
]) }}>
    @if ($highlight)
        <span class="absolute -top-3.5 left-6 rounded-full bg-cafe px-3 py-1 text-xs font-bold text-white">Mais escolhido</span>
    @endif

    <div class="flex flex-col gap-1.5">
        <div class="flex items-center justify-between gap-3">
            <h3 class="font-display text-2xl font-semibold">{{ $plan->label() }}</h3>
            @if ($current)
                <span class="selo border-transparent bg-[#E7F4EC] text-[#1C5234]">Seu plano</span>
            @endif
        </div>
        <p class="text-sm {{ $highlight ? 'text-menu-texto' : 'text-suave' }}">{{ $plan->description() }}</p>
    </div>

    <p class="flex items-baseline gap-1">
        <span class="text-lg font-semibold">R$</span>
        <span class="text-[44px] font-bold leading-none tracking-tight">{{ number_format($plan->price(), 2, ',', '.') }}</span>
        <span class="text-sm {{ $highlight ? 'text-menu-texto' : 'text-suave' }}">/mês</span>
    </p>

    <ul class="flex flex-col gap-2.5 text-[15px]">
        @foreach ($plan->features() as $feature)
            <li class="flex items-start gap-2.5">
                <x-icon name="check" class="mt-0.5 h-[18px] w-[18px] {{ $highlight ? 'text-[#D9A57F]' : 'text-cafe' }}" />
                <span>
                    {{ $feature['text'] }}
                    @if ($feature['soon'])
                        <span class="ml-1 inline-block rounded-full border border-dashed px-2 py-px text-[11px] font-semibold uppercase tracking-wide {{ $highlight ? 'border-menu-suave text-menu-suave' : 'border-linha-forte text-suave' }}">Em breve</span>
                    @endif
                </span>
            </li>
        @endforeach
    </ul>

    @if ($slot->isNotEmpty())
        <div class="mt-auto pt-1">{{ $slot }}</div>
    @endif
</article>
