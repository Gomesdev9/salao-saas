@props(['month'])

@php
    $tabs = [
        ['route' => 'finance.index', 'label' => 'Visão geral'],
        ['route' => 'finance.entries', 'label' => 'Lançamentos'],
        ['route' => 'finance.commissions', 'label' => 'Comissões'],
    ];
@endphp

<nav aria-label="Seções do financeiro" class="flex gap-1 overflow-x-auto border-b border-linha">
    @foreach ($tabs as $tab)
        @php
            $active = request()->routeIs($tab['route']);
        @endphp
        <a href="{{ route($tab['route'], ['mes' => $month->format('Y-m')]) }}" @if ($active) aria-current="page" @endif
           class="-mb-px inline-flex min-h-11 shrink-0 items-center border-b-2 px-3.5 text-sm {{ $active ? 'border-cafe font-bold text-tinta' : 'border-transparent font-semibold text-suave hover:text-tinta' }}">{{ $tab['label'] }}</a>
    @endforeach
</nav>
