@php
    $items = [
        ['route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'painel', 'label' => 'Painel'],
        ['route' => 'appointments.index', 'active' => 'appointments.*', 'icon' => 'agenda', 'label' => 'Agenda'],
        ['route' => 'clients.index', 'active' => 'clients.*', 'icon' => 'clientes', 'label' => 'Clientes'],
        ['route' => 'professionals.index', 'active' => 'professionals.*', 'icon' => 'profissionais', 'label' => 'Profissionais'],
        ['route' => 'services.index', 'active' => 'services.*', 'icon' => 'servicos', 'label' => 'Serviços'],
    ];
    $user = Auth::user();
@endphp

{{-- Menu lateral flutuante (computador). No celular quem aparece é o menu de baixo (layouts.bottom-nav). --}}
<aside class="m-4 hidden w-[236px] shrink-0 flex-col gap-7 rounded-menu bg-tinta px-4 py-6 text-creme shadow-menu lg:sticky lg:top-4 lg:flex lg:h-[calc(100vh-2rem)]">
    <a href="{{ route('dashboard') }}" class="min-w-0 px-2">
        <x-logo :subtitle="$user->salon->name" />
    </a>

    <div class="flex flex-1 flex-col gap-7">
        <nav aria-label="Menu principal" class="flex flex-col gap-1">
            @foreach ($items as $item)
                @php
                    $active = request()->routeIs($item['active']);
                @endphp
                <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                   class="flex min-h-11 items-center gap-3 rounded-full px-3 text-sm transition {{ $active ? 'bg-menu-ativo font-semibold text-white' : 'font-medium text-menu-texto hover:bg-menu-ativo/60 hover:text-white' }}">
                    <x-icon :name="$item['icon']" class="h-[18px] w-[18px]" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="mt-auto flex items-center gap-2.5 border-t border-menu-ativo px-2 pt-3">
            <x-avatar :name="$user->name" size="h-8 w-8 text-xs" class="!bg-menu-ativo !text-creme" />
            <a href="{{ route('profile.edit') }}" class="min-w-0 flex-1 truncate text-[13px] text-menu-texto hover:text-white" title="Meu perfil">{{ $user->name }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex h-11 w-11 items-center justify-center rounded-full text-menu-texto hover:bg-menu-ativo hover:text-white">
                    <span class="sr-only">Sair</span>
                    <x-icon name="sair" class="h-[18px] w-[18px]" />
                </button>
            </form>
        </div>
    </div>
</aside>
