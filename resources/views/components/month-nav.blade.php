@props(['month', 'route'])

@php
    $label = \Illuminate\Support\Str::ucfirst($month->translatedFormat('M Y'));
@endphp

{{-- Troca de mês: ‹ Out 2026 › --}}
<div class="flex items-center rounded-campo border border-linha bg-cartao">
    <a href="{{ route($route, ['mes' => $month->copy()->subMonth()->format('Y-m')]) }}" class="flex h-11 w-11 items-center justify-center rounded-l-campo hover:bg-areia">
        <span class="sr-only">Mês anterior</span>
        <x-icon name="chevron-left" class="h-[18px] w-[18px]" />
    </a>
    <span class="px-2.5 text-sm font-semibold">{{ $label }}</span>
    <a href="{{ route($route, ['mes' => $month->copy()->addMonth()->format('Y-m')]) }}" class="flex h-11 w-11 items-center justify-center rounded-r-campo hover:bg-areia">
        <span class="sr-only">Próximo mês</span>
        <x-icon name="chevron-right" class="h-[18px] w-[18px]" />
    </a>
</div>
