@props(['name'])

{{-- Ícones de traço (estilo do design). Uso: <x-icon name="agenda" class="h-5 w-5" /> --}}
<svg {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('painel')
            <rect x="3" y="3" width="7" height="9" rx="1" /><rect x="14" y="3" width="7" height="5" rx="1" /><rect x="14" y="12" width="7" height="9" rx="1" /><rect x="3" y="16" width="7" height="5" rx="1" />
            @break
        @case('agenda')
            <rect x="3" y="5" width="18" height="16" rx="2" /><path d="M3 10h18M8 3v4M16 3v4" />
            @break
        @case('clientes')
            <circle cx="9" cy="8" r="4" /><path d="M2 21c0-4 3-6 7-6s7 2 7 6M16 4a4 4 0 0 1 0 8M22 21c0-3-2-5-4-5.5" />
            @break
        @case('profissionais')
            <circle cx="6" cy="6" r="3" /><circle cx="6" cy="18" r="3" /><path d="M8.1 8.1 20 20M8.1 15.9 20 4" />
            @break
        @case('servicos')
            <path d="M4 6h16M4 12h16M4 18h10" />
            @break
        @case('financeiro')
            <rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20M16 15h2" />
            @break
        @case('sair')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
            @break
        @case('mais')
            <path d="M12 5v14M5 12h14" />
            @break
        @case('chevron-left')
            <path d="M15 18l-6-6 6-6" />
            @break
        @case('chevron-right')
            <path d="M9 18l6-6-6-6" />
            @break
        @case('busca')
            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
            @break
        @case('check')
            <path d="M20 6 9 17l-5-5" />
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16" />
            @break
        @case('fechar')
            <path d="M6 6l12 12M18 6 6 18" />
            @break
        @case('link')
            <path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7" /><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7" />
            @break
        @case('cadeado')
            <rect x="4" y="11" width="16" height="10" rx="2" /><path d="M8 11V7a4 4 0 0 1 8 0v4" />
            @break
        @case('seta')
            <path d="M5 12h14M13 6l6 6-6 6" />
            @break
        @case('whatsapp')
            <path d="M3 21l1.7-5A8.5 8.5 0 1 1 8 19.3L3 21z" /><path d="M9 10c.5 2 2 3.5 4 4l1.2-1.2 2 1-.4 1.6c-3.5.3-7.4-3.6-7.1-7.1L10.3 8l1 2L10 11.2" />
            @break
    @endswitch
</svg>
