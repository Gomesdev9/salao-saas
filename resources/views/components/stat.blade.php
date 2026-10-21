@props(['label', 'value', 'hint' => null, 'href' => null, 'highlight' => false])

@php
    $tag = $href ? 'a' : 'div';
    $classes = $highlight
        ? 'rounded-cartao bg-cafe text-white shadow-cartao hover:bg-cafe-escuro'
        : 'cartao text-tinta' . ($href ? ' hover:bg-areia' : '');
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => "flex flex-col gap-1 px-5 py-4 transition {$classes}"]) }}>
    <span class="text-[13px] {{ $highlight ? 'text-pele' : 'text-suave' }}">{{ $label }}</span>
    <span class="text-2xl font-bold">{{ $value }}</span>
    @if ($hint)
        <span class="text-[13px] {{ $highlight ? 'text-pele' : 'text-suave' }}">{{ $hint }}</span>
    @endif
</{{ $tag }}>
