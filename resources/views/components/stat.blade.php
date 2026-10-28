@props(['label', 'value', 'hint' => null, 'href' => null, 'highlight' => false, 'dark' => false])

@php
    $tag = $href ? 'a' : 'div';
    $classes = match (true) {
        $highlight => 'rounded-cartao bg-cafe text-white shadow-cartao hover:bg-cafe-escuro',
        $dark => 'rounded-cartao bg-tinta text-creme shadow-cartao',
        default => 'cartao text-tinta' . ($href ? ' hover:bg-areia' : ''),
    };
    $muted = match (true) {
        $highlight => 'text-pele',
        $dark => 'text-menu-suave',
        default => 'text-suave',
    };
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => "flex flex-col gap-1 px-5 py-4 transition {$classes}"]) }}>
    <span class="text-[13px] {{ $muted }}">{{ $label }}</span>
    <span class="text-2xl font-bold">{{ $value }}</span>
    @if ($hint)
        <span class="text-[13px] {{ $muted }}">{{ $hint }}</span>
    @endif
</{{ $tag }}>
