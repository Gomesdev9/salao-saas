@props(['variant' => 'primary', 'icon' => null])

@php
    $classes = $variant === 'primary'
        ? 'bg-cafe text-white hover:bg-cafe-escuro'
        : 'border border-linha bg-cartao text-tinta hover:bg-areia';
@endphp

<a {{ $attributes->merge(['class' => "inline-flex h-11 items-center justify-center gap-2 rounded-campo px-5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-caramelo focus:ring-offset-2 {$classes}"]) }}>
    @if ($icon)
        <x-icon :name="$icon" class="h-[18px] w-[18px]" />
    @endif
    {{ $slot }}
</a>
