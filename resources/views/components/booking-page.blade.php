@props(['salon', 'title', 'step' => null, 'back' => null, 'account' => null, 'close' => null])

{{-- Página pública de agendamento da cliente: pensada para o celular, centralizada no computador --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} · {{ $salon->name }}</title>

        @include('layouts.fonts')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="mx-auto flex min-h-screen w-full max-w-[480px] flex-col gap-5 px-4 pb-36 pt-4">
            <header class="grid grid-cols-[44px_1fr_44px] items-center gap-2">
                @if ($back)
                    <a href="{{ $back }}" class="flex h-11 w-11 items-center justify-center rounded-full border border-linha bg-cartao shadow-cartao hover:bg-areia">
                        <span class="sr-only">Voltar</span><x-icon name="chevron-left" class="h-5 w-5" />
                    </a>
                @else
                    <span></span>
                @endif

                <span class="truncate text-center font-display text-xl italic">{{ $salon->name }}</span>

                @if ($close)
                    <a href="{{ $close }}" class="flex h-11 w-11 items-center justify-center rounded-full border border-linha bg-cartao shadow-cartao hover:bg-areia">
                        <span class="sr-only">Fechar</span><x-icon name="fechar" class="h-5 w-5" />
                    </a>
                @elseif ($account)
                    <a href="{{ $account }}" class="flex h-11 w-11 items-center justify-center rounded-full border border-linha bg-cartao shadow-cartao hover:bg-areia">
                        <span class="sr-only">Meus horários</span><x-icon name="usuario" class="h-5 w-5" />
                    </a>
                @else
                    <span></span>
                @endif
            </header>

            @isset($heading)
                <div class="flex flex-col gap-2">
                    @if ($step)
                        <span class="text-xs font-bold uppercase tracking-[0.18em] text-cafe">Passo {{ $step }} de 4</span>
                    @endif
                    <h1 class="text-[30px] font-medium leading-[1.12] [&_em]:italic [&_em]:text-cafe">{{ $heading }}</h1>
                    @if ($step)
                        <div class="mt-2 grid grid-cols-4 gap-1.5" aria-hidden="true">
                            @for ($i = 1; $i <= 4; $i++)
                                <span class="h-[3px] rounded-full {{ $i <= $step ? 'bg-cafe' : 'bg-linha-forte' }}"></span>
                            @endfor
                        </div>
                    @endif
                </div>
            @endisset

            {{ $slot }}
        </div>

        @isset($bar)
            {{-- Barra fixa no rodapé com o resumo e o botão de seguir --}}
            <div class="fixed inset-x-0 bottom-0 z-30 px-4 pb-4">
                <div class="mx-auto flex max-w-[448px] items-center justify-between gap-3 rounded-full bg-tinta py-2.5 pl-6 pr-2.5 text-creme shadow-menu">
                    {{ $bar }}
                </div>
            </div>
        @endisset
    </body>
</html>
