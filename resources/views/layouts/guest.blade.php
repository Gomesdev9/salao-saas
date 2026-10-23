<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        @include('layouts.fonts')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-wrap">
            {{-- Painel escuro com a apresentação --}}
            <section class="flex flex-[1_1_420px] flex-col justify-between gap-10 bg-tinta p-8 text-creme sm:p-12">
                <a href="/">
                    <x-logo size="h-10 w-10 text-xl" />
                </a>
                <div class="flex max-w-[460px] flex-col gap-6">
                    @isset($aside)
                        {{ $aside }}
                    @else
                        <h1 class="text-4xl font-bold leading-tight sm:text-[40px]">A agenda do seu salão, <em class="italic text-[#D9A57F]">organizada</em>.</h1>
                    @endisset
                </div>
                <span class="text-[13px] text-menu-suave">Cada empresa vê só os próprios dados.</span>
            </section>

            {{-- Formulário --}}
            <section class="flex flex-[1_1_440px] items-center justify-center px-4 py-10 sm:px-6 sm:py-12">
                <div class="w-full max-w-[460px] rounded-[28px] border border-linha bg-cartao p-6 shadow-cartao sm:p-9">
                    {{ $slot }}
                </div>
            </section>
        </div>
    </body>
</html>
