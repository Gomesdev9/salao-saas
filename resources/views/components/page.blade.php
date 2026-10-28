@props(['title', 'subtitle' => null, 'eyebrow' => null, 'back' => null, 'backLabel' => 'Voltar'])

<x-app-layout :title="$title">
    <div class="flex flex-col gap-5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-col gap-1">
                @if ($back)
                    <a href="{{ $back }}" class="mb-1 inline-flex items-center gap-1.5 text-sm font-semibold text-cafe hover:text-cafe-escuro">
                        <x-icon name="chevron-left" class="h-4 w-4" />{{ $backLabel }}
                    </a>
                @endif
                @if ($eyebrow)
                    <span class="text-[13px] font-semibold uppercase tracking-wider text-cafe">{{ $eyebrow }}</span>
                @endif
                <h1 class="text-[28px] font-semibold leading-tight sm:text-[32px]">{{ $title }}</h1>
                @if ($subtitle)
                    <span class="text-sm text-suave">{{ $subtitle }}</span>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>

        <x-flash />

        {{ $slot }}
    </div>
</x-app-layout>
