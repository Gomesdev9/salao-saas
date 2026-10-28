@use('App\Enums\AppointmentStatus')
@use('App\Enums\BusinessType')
@use('App\Enums\Plan')

@php
    $features = [
        ['icon' => 'agenda', 'title' => 'Agenda por profissional', 'text' => 'O dia inteiro numa tela, com uma coluna para cada profissional. O sistema não deixa marcar dois horários ao mesmo tempo com a mesma pessoa.'],
        ['icon' => 'clientes', 'title' => 'Clientes', 'text' => 'WhatsApp, aniversário e observações de cada cliente, com busca por nome ou telefone. Os aniversariantes da semana aparecem no painel.'],
        ['icon' => 'servicos', 'title' => 'Serviços', 'text' => 'Categoria, duração e preço de cada serviço. Ao agendar, o horário de término e o valor já saem do serviço escolhido.'],
        ['icon' => 'profissionais', 'title' => 'Profissionais e comissões', 'text' => 'Cadastre a equipe, os serviços que cada profissional faz e a porcentagem de comissão de cada um.'],
        ['icon' => 'painel', 'title' => 'Painel do dia', 'text' => 'Assim que você entra: atendimentos de hoje, valor previsto e os horários que ainda faltam confirmar.'],
        ['icon' => 'financeiro', 'title' => 'Financeiro e caixa', 'text' => 'Receba no Pix, dinheiro ou cartão direto do horário, acompanhe as despesas a vencer, o lucro do mês e quanto pagar de comissão a cada profissional.'],
        ['icon' => 'link', 'title' => 'Agendamento online', 'text' => 'Um link da sua empresa para a cliente escolher o procedimento, o profissional e um horário livre. O horário cai direto na agenda, e ela remarca ou cancela pelo celular.'],
        ['icon' => 'cadeado', 'title' => 'Dados só seus', 'text' => 'Cada empresa tem a própria conta e só enxerga os próprios clientes, profissionais e horários.'],
    ];

    $comingSoon = [
        ['icon' => 'whatsapp', 'title' => 'Lembretes por WhatsApp'],
    ];

    $steps = [
        ['title' => 'Cadastre sua empresa', 'text' => 'Nome, tipo de negócio, WhatsApp e os seus dados de acesso. Ao terminar, você já entra no sistema.'],
        ['title' => 'Monte serviços e equipe', 'text' => 'Cadastre os serviços com preço e duração e os profissionais que atendem cada um.'],
        ['title' => 'Comece a agendar', 'text' => 'Marque os horários na agenda e acompanhe o dia pelo painel, no computador ou no celular.'],
    ];

    $faq = [
        ['q' => 'Preciso instalar algum programa?', 'a' => 'Não. O sistema funciona no navegador, tanto no computador quanto no celular.'],
        ['q' => 'Outras empresas conseguem ver os meus dados?', 'a' => 'Não. Cada empresa tem a própria conta, e o sistema separa os dados de cada uma: clientes, profissionais, serviços e horários.'],
        ['q' => 'Como funciona o teste grátis?', 'a' => 'Você escolhe o plano no cadastro e usa o sistema completo por '.Plan::TRIAL_DAYS.' dias, sem cartão de crédito.'],
        ['q' => 'Posso trocar de plano depois?', 'a' => 'Pode. Na tela "Meu plano" você muda para outro plano quando quiser, inclusive durante o teste.'],
        ['q' => 'Quantos profissionais posso cadastrar?', 'a' => 'No Essencial, até 2 profissionais ativos. No Profissional, até 6. No Premium não tem limite. Profissionais inativos não contam.'],
        ['q' => 'Quem faz o cadastro da empresa?', 'a' => 'O proprietário. Quem cadastra a empresa vira o administrador da conta e entra com o próprio e-mail e senha.'],
    ];

    // Horários de exemplo da prévia da agenda: [hora, profissional (0 ou 1), cliente, serviço, situação]
    $preview = [
        ['09:00', 0, 'Débora', 'Escova', AppointmentStatus::Done],
        ['09:30', 1, 'Carla', 'Manicure', AppointmentStatus::Confirmed],
        ['10:30', 0, 'Júlia', 'Corte feminino', AppointmentStatus::Confirmed],
        ['11:00', 1, 'Renata', 'Pé e mão', AppointmentStatus::Scheduled],
        ['13:30', 0, 'Paula', 'Coloração', AppointmentStatus::Scheduled],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Agenda, clientes, serviços e comissões da sua empresa de beleza num só lugar. Cadastre sua empresa e comece a usar.">

        <title>{{ config('app.name') }} · Sistema para salões e clínicas de estética</title>

        @include('layouts.fonts')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        {{-- Menu flutuante --}}
        <header class="sticky top-0 z-30 px-3 pt-3 sm:px-6">
            <div class="mx-auto flex max-w-[1180px] items-center justify-between gap-2 rounded-full border border-linha bg-cartao/95 py-1.5 pl-4 pr-1.5 shadow-cartao backdrop-blur sm:pl-5">
                <a href="{{ route('home') }}" class="min-w-0">
                    <x-logo />
                </a>
                <nav aria-label="Seções da página" class="hidden items-center gap-1 lg:flex">
                    @foreach (['recursos' => 'Recursos', 'como-funciona' => 'Como funciona', 'para-quem' => 'Para quem é', 'planos' => 'Planos', 'duvidas' => 'Dúvidas'] as $anchor => $label)
                        <a href="#{{ $anchor }}" class="inline-flex h-11 items-center rounded-full px-4 text-sm font-semibold text-suave transition hover:bg-areia hover:text-tinta">{{ $label }}</a>
                    @endforeach
                </nav>
                <div class="flex shrink-0 items-center gap-1">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex h-11 items-center gap-2 rounded-full bg-cafe px-5 text-sm font-semibold text-white transition hover:bg-cafe-escuro">Ir para o painel</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex h-11 items-center rounded-full px-3 text-sm font-semibold transition hover:bg-areia sm:px-4">Entrar</a>
                        <a href="{{ route('register') }}" class="inline-flex h-11 items-center rounded-full bg-cafe px-4 text-sm font-semibold text-white transition hover:bg-cafe-escuro sm:px-5">
                            Cadastrar<span class="hidden sm:inline">&nbsp;empresa</span>
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="overflow-x-clip">
            {{-- Abertura --}}
            <section class="mx-auto grid max-w-[1180px] items-center gap-12 px-4 pb-16 pt-12 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:pb-24 lg:pt-20">
                <div class="flex flex-col gap-6">
                    <span class="text-[13px] font-bold uppercase tracking-[0.16em] text-cafe">Sistema para salões e clínicas de estética</span>
                    <h1 class="text-[40px] font-semibold leading-[1.05] sm:text-[56px]">
                        A agenda da sua empresa, <em class="italic text-cafe">organizada</em> do primeiro ao último horário.
                    </h1>
                    <p class="max-w-[540px] text-lg leading-relaxed text-suave">
                        Agenda por profissional, clientes, serviços e comissões num só lugar. Funciona no navegador do computador e do celular, sem instalar nada.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex h-14 items-center gap-2 rounded-full bg-cafe px-7 text-base font-semibold text-white shadow-[0_14px_28px_-14px_rgba(146,88,55,0.8)] transition hover:bg-cafe-escuro">
                                Ir para o painel<x-icon name="seta" class="h-[18px] w-[18px]" />
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex h-14 items-center gap-2 rounded-full bg-cafe px-7 text-base font-semibold text-white shadow-[0_14px_28px_-14px_rgba(146,88,55,0.8)] transition hover:bg-cafe-escuro">
                                Cadastrar minha empresa<x-icon name="seta" class="h-[18px] w-[18px]" />
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex h-14 items-center rounded-full border border-linha-forte bg-cartao px-7 text-base font-semibold transition hover:bg-areia">Já tenho conta</a>
                        @endauth
                    </div>
                    <ul class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-suave">
                        @foreach (['Teste grátis por '.Plan::TRIAL_DAYS.' dias', 'Sem instalar nada', 'Dados separados por empresa'] as $point)
                            <li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-cafe" />{{ $point }}</li>
                        @endforeach
                    </ul>
                </div>

                {{-- Prévia da agenda, feita em HTML com as cores reais dos status --}}
                <div class="relative mx-auto w-full max-w-[520px]">
                    <div class="absolute -inset-3 -rotate-2 rounded-[36px] bg-pele/70 sm:-inset-5" aria-hidden="true"></div>
                    <figure class="cartao relative overflow-hidden" role="img" aria-label="Exemplo da agenda do dia com horários de duas profissionais">
                        <div class="flex items-center justify-between gap-3 border-b border-linha px-5 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold uppercase tracking-wider text-cafe">Agenda de hoje</span>
                                <span class="font-display text-xl font-semibold">Quarta, 21 de outubro</span>
                            </div>
                            <span class="selo border-[#3E9A63] bg-[#E7F4EC] text-[#1C5234]">5 horários</span>
                        </div>
                        <div class="grid grid-cols-2 border-b border-linha bg-areia text-sm font-semibold">
                            @foreach (['Ana', 'Bruna'] as $professional)
                                <div class="flex items-center gap-2 px-4 py-2.5 {{ $loop->first ? 'border-r border-linha' : '' }}">
                                    <span class="avatar h-7 w-7 text-xs">{{ mb_substr($professional, 0, 1) }}</span>{{ $professional }}
                                </div>
                            @endforeach
                        </div>
                        <div class="flex flex-col gap-2 p-4">
                            @foreach ($preview as [$time, $column, $client, $service, $status])
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="{{ $column === 1 ? 'col-start-2' : '' }} flex flex-col gap-0.5 rounded-campo border-l-4 px-3 py-2 {{ $status->color() }}">
                                        <span class="flex items-center justify-between gap-2 text-xs font-bold">{{ $time }}<span class="font-semibold">{{ $status->label() }}</span></span>
                                        <span class="truncate text-sm font-semibold text-tinta">{{ $client }}</span>
                                        <span class="truncate text-xs text-suave">{{ $service }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </figure>
                    <div class="absolute -bottom-6 right-4 flex items-center gap-3 rounded-[18px] bg-tinta px-4 py-3 text-creme shadow-menu sm:-right-6" aria-hidden="true">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-menu-ativo text-[#D9A57F]"><x-icon name="financeiro" class="h-[18px] w-[18px]" /></span>
                        <span class="flex flex-col">
                            <span class="text-xs text-menu-suave">Previsto hoje</span>
                            <span class="text-base font-bold">R$ 420,00</span>
                        </span>
                    </div>
                </div>
            </section>

            {{-- Recursos --}}
            <section id="recursos" class="mx-auto flex max-w-[1180px] scroll-mt-24 flex-col gap-10 px-4 py-16 sm:px-6">
                <div class="flex max-w-[640px] flex-col gap-3">
                    <span class="text-[13px] font-bold uppercase tracking-[0.16em] text-cafe">Recursos</span>
                    <h2 class="text-[32px] font-semibold leading-tight sm:text-[40px]">Tudo o que o <em class="italic text-cafe">dia a dia</em> da sua empresa precisa</h2>
                    <p class="text-base leading-relaxed text-suave">Do primeiro cadastro ao último atendimento do dia, sem planilha e sem caderno.</p>
                </div>
                <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($features as $feature)
                        {{-- Com 7 recursos, o último ocupa a linha inteira para a grade não ficar com um cartão sozinho --}}
                        <li @class(['cartao flex flex-col gap-3 p-6', 'sm:col-span-2 lg:col-span-3' => $loop->last && $loop->count % 3 === 1])>
                            <span class="flex h-12 w-12 items-center justify-center rounded-campo bg-pele text-cafe-escuro"><x-icon :name="$feature['icon']" class="h-[22px] w-[22px]" /></span>
                            <h3 class="text-lg font-bold">{{ $feature['title'] }}</h3>
                            <p class="text-[15px] leading-relaxed text-suave">{{ $feature['text'] }}</p>
                        </li>
                    @endforeach
                </ul>
                <div class="flex flex-col gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-suave">Em breve</h3>
                    <ul class="flex flex-wrap gap-3">
                        @foreach ($comingSoon as $item)
                            <li class="flex min-h-12 items-center gap-2.5 rounded-full border border-dashed border-linha-forte bg-cartao px-4 text-sm font-semibold">
                                <x-icon :name="$item['icon']" class="h-[18px] w-[18px] text-cafe" />{{ $item['title'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            {{-- Como funciona --}}
            <section id="como-funciona" class="scroll-mt-24 px-3 py-8 sm:px-6">
                <div class="mx-auto flex max-w-[1180px] flex-col gap-10 rounded-[36px] bg-tinta px-6 py-12 text-creme shadow-menu sm:px-12 sm:py-16">
                    <div class="flex max-w-[760px] flex-col gap-3">
                        <span class="text-[13px] font-bold uppercase tracking-[0.16em] text-[#D9A57F]">Como funciona</span>
                        <h2 class="text-[32px] font-semibold leading-tight sm:text-[40px]">Do cadastro à agenda em <em class="italic text-[#D9A57F]">três passos</em></h2>
                    </div>
                    <ol class="grid gap-6 md:grid-cols-3">
                        @foreach ($steps as $step)
                            <li class="flex flex-col gap-3 rounded-cartao bg-[#2D1A10] p-6">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-cafe text-base font-bold text-white">{{ $loop->iteration }}</span>
                                <h3 class="text-lg font-bold">{{ $step['title'] }}</h3>
                                <p class="text-[15px] leading-relaxed text-menu-texto">{{ $step['text'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                    @guest
                        <a href="{{ route('register') }}" class="inline-flex h-14 items-center gap-2 self-start rounded-full bg-pele px-7 text-base font-semibold text-tinta transition hover:bg-white">
                            Começar o cadastro<x-icon name="seta" class="h-[18px] w-[18px]" />
                        </a>
                    @endguest
                </div>
            </section>

            {{-- Para quem é --}}
            <section id="para-quem" class="mx-auto flex max-w-[1180px] scroll-mt-24 flex-col gap-8 px-4 py-16 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex max-w-[520px] flex-col gap-3">
                    <span class="text-[13px] font-bold uppercase tracking-[0.16em] text-cafe">Para quem é</span>
                    <h2 class="text-[32px] font-semibold leading-tight sm:text-[40px]">Feito para negócios de beleza que <em class="italic text-cafe">vivem de agenda</em></h2>
                </div>
                <ul class="flex max-w-[560px] flex-wrap gap-3">
                    @foreach (BusinessType::cases() as $type)
                        @continue($type === BusinessType::Other)
                        <li class="flex min-h-12 items-center rounded-full border border-linha bg-cartao px-5 text-[15px] font-semibold shadow-cartao">{{ $type->label() }}</li>
                    @endforeach
                </ul>
            </section>

            {{-- Planos --}}
            <section id="planos" class="mx-auto flex max-w-[1180px] scroll-mt-24 flex-col gap-10 px-4 py-16 sm:px-6">
                <div class="flex max-w-[680px] flex-col gap-3">
                    <span class="text-[13px] font-bold uppercase tracking-[0.16em] text-cafe">Planos</span>
                    <h2 class="text-[32px] font-semibold leading-tight sm:text-[40px]">Um plano do <em class="italic text-cafe">tamanho da sua equipe</em></h2>
                    <p class="text-base leading-relaxed text-suave">Todos começam com {{ Plan::TRIAL_DAYS }} dias grátis, sem cartão de crédito. Você pode trocar de plano quando quiser.</p>
                </div>
                <div class="grid gap-6 pt-3 lg:grid-cols-3">
                    @foreach (Plan::cases() as $plan)
                        <x-plan-card :plan="$plan">
                            @php
                                $button = $plan->isHighlighted() ? 'bg-pele text-tinta hover:bg-white' : 'bg-cafe text-white hover:bg-cafe-escuro';
                            @endphp
                            @auth
                                <a href="{{ route('plan.edit') }}" class="inline-flex h-12 w-full items-center justify-center rounded-full text-[15px] font-semibold transition {{ $button }}">Ver meu plano</a>
                            @else
                                <a href="{{ route('register', ['plano' => $plan->value]) }}" class="inline-flex h-12 w-full items-center justify-center rounded-full text-[15px] font-semibold transition {{ $button }}">
                                    Testar grátis por {{ Plan::TRIAL_DAYS }} dias
                                </a>
                            @endauth
                        </x-plan-card>
                    @endforeach
                </div>
            </section>

            {{-- Dúvidas --}}
            <section id="duvidas" class="mx-auto flex max-w-[860px] scroll-mt-24 flex-col gap-8 px-4 py-16 sm:px-6">
                <div class="flex flex-col gap-3 text-center">
                    <span class="text-[13px] font-bold uppercase tracking-[0.16em] text-cafe">Dúvidas</span>
                    <h2 class="text-[32px] font-semibold leading-tight sm:text-[40px]">Perguntas frequentes</h2>
                </div>
                <div class="flex flex-col gap-3">
                    @foreach ($faq as $item)
                        <details class="cartao group px-6 py-1" @if ($loop->first) open @endif>
                            <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 text-base font-bold [&::-webkit-details-marker]:hidden">
                                {{ $item['q'] }}
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pele text-cafe-escuro transition group-open:rotate-45"><x-icon name="mais" class="h-4 w-4" /></span>
                            </summary>
                            <p class="pb-5 text-[15px] leading-relaxed text-suave">{{ $item['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>

            {{-- Chamada final --}}
            <section class="px-3 pb-12 pt-4 sm:px-6">
                <div class="mx-auto flex max-w-[1180px] flex-col items-start gap-6 rounded-[36px] bg-pele px-6 py-12 sm:px-12 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex max-w-[620px] flex-col gap-3">
                        <h2 class="text-[32px] font-semibold leading-tight sm:text-[40px]">Vamos organizar a <em class="italic text-cafe">sua agenda</em>?</h2>
                        <p class="text-base leading-relaxed text-suave">Cadastre a sua empresa, entre no sistema na mesma hora e use {{ Plan::TRIAL_DAYS }} dias grátis.</p>
                    </div>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex h-14 shrink-0 items-center gap-2 rounded-full bg-tinta px-7 text-base font-semibold text-creme transition hover:bg-menu-ativo">Ir para o painel<x-icon name="seta" class="h-[18px] w-[18px]" /></a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex h-14 shrink-0 items-center gap-2 rounded-full bg-tinta px-7 text-base font-semibold text-creme transition hover:bg-menu-ativo">Cadastrar minha empresa<x-icon name="seta" class="h-[18px] w-[18px]" /></a>
                    @endauth
                </div>
            </section>
        </main>

        <footer class="mx-auto flex max-w-[1180px] flex-wrap items-center justify-between gap-4 border-t border-linha px-4 py-8 text-sm text-suave sm:px-6">
            <x-logo class="text-tinta" />
            <span>© {{ now()->year }} {{ config('app.name') }}</span>
            <nav aria-label="Acesso" class="flex gap-1">
                <a href="{{ route('login') }}" class="inline-flex h-11 items-center rounded-full px-4 font-semibold text-tinta hover:bg-areia">Entrar</a>
                <a href="{{ route('register') }}" class="inline-flex h-11 items-center rounded-full px-4 font-semibold text-cafe hover:bg-areia">Cadastrar empresa</a>
            </nav>
        </footer>
    </body>
</html>
