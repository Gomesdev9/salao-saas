@props(['name', 'size' => 'h-[34px] w-[34px] text-xs'])

@php
    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->map(fn ($word) => mb_substr($word, 0, 1))
        ->take(2)
        ->join('');
@endphp

<span {{ $attributes->merge(['class' => "avatar {$size}"]) }} aria-hidden="true">{{ mb_strtoupper($initials) }}</span>
