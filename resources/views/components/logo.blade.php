@props(['subtitle' => null, 'size' => 'h-9 w-9 text-lg'])

<span {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <span class="flex {{ $size }} items-center justify-center rounded-campo bg-caramelo font-bold text-white" aria-hidden="true">S</span>
    <span class="flex flex-col">
        <span class="text-[15px] font-bold">{{ config('app.name') }}</span>
        @if ($subtitle)
            <span class="text-xs text-menu-suave">{{ $subtitle }}</span>
        @endif
    </span>
</span>
