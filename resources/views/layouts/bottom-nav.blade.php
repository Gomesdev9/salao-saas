@php
    $items = [
        ['route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'painel', 'label' => 'Painel'],
        ['route' => 'appointments.index', 'active' => 'appointments.*', 'icon' => 'agenda', 'label' => 'Agenda'],
        ['route' => 'clients.index', 'active' => 'clients.*', 'icon' => 'clientes', 'label' => 'Clientes'],
    ];
    // Telas que ficam dentro do "Mais"
    $more = [
        ['route' => 'professionals.index', 'active' => 'professionals.*', 'icon' => 'profissionais', 'label' => 'Profissionais'],
        ['route' => 'services.index', 'active' => 'services.*', 'icon' => 'servicos', 'label' => 'Serviços'],
        ['route' => 'finance.index', 'active' => 'finance.*', 'icon' => 'financeiro', 'label' => 'Financeiro'],
        ['route' => 'plan.edit', 'active' => 'plan.*', 'icon' => 'plano', 'label' => 'Meu plano'],
    ];
    $moreActive = request()->routeIs('professionals.*', 'services.*', 'finance.*', 'plan.*', 'profile.*');
    $user = Auth::user();
@endphp

{{-- Menu flutuante do celular, no rodapé da tela --}}
<div x-data="{ more: false }" @keydown.escape.window="more = false" @click.outside="more = false" class="fixed inset-x-3.5 bottom-3.5 z-40 lg:hidden">
    <div id="menu-mais" x-show="more" x-cloak
         class="mb-2 flex flex-col gap-1 rounded-[26px] bg-tinta p-2 text-creme shadow-menu">
        @foreach ($more as $item)
            @php
                $active = request()->routeIs($item['active']);
            @endphp
            <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
               class="flex min-h-12 items-center gap-3 rounded-full px-4 text-sm {{ $active ? 'bg-menu-ativo font-semibold text-white' : 'font-medium text-menu-texto' }}">
                <x-icon :name="$item['icon']" class="h-[18px] w-[18px]" />{{ $item['label'] }}
            </a>
        @endforeach
        <div class="flex items-center gap-2.5 border-t border-menu-ativo px-2 pt-2">
            <x-avatar :name="$user->name" size="h-8 w-8 text-xs" class="!bg-menu-ativo !text-creme" />
            <a href="{{ route('profile.edit') }}" class="flex min-h-11 min-w-0 flex-1 items-center truncate text-[13px] text-menu-texto">{{ $user->name }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex h-11 w-11 items-center justify-center rounded-full text-menu-texto hover:bg-menu-ativo hover:text-white">
                    <span class="sr-only">Sair</span>
                    <x-icon name="sair" class="h-[18px] w-[18px]" />
                </button>
            </form>
        </div>
    </div>

    <nav aria-label="Menu principal" class="grid h-[68px] grid-cols-4 rounded-full bg-tinta shadow-menu">
        @foreach ($items as $item)
            @php
                $active = request()->routeIs($item['active']);
            @endphp
            <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
               class="flex flex-col items-center justify-center gap-1 rounded-full text-[11px] {{ $active ? 'm-1.5 bg-menu-ativo font-bold text-white' : 'font-semibold text-menu-texto' }}">
                <x-icon :name="$item['icon']" class="h-[22px] w-[22px]" />{{ $item['label'] }}
            </a>
        @endforeach
        <button type="button" @click="more = !more" :aria-expanded="more.toString()" aria-controls="menu-mais"
                class="flex flex-col items-center justify-center gap-1 rounded-full text-[11px] {{ $moreActive ? 'm-1.5 bg-menu-ativo font-bold text-white' : 'font-semibold text-menu-texto' }}">
            <x-icon name="menu" class="h-[22px] w-[22px]" />Mais
        </button>
    </nav>
</div>
