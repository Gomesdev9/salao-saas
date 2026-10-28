<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? "{$title} · " : '' }}{{ config('app.name') }}</title>

        @include('layouts.fonts')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen lg:flex">
            @include('layouts.sidebar')

            <main class="min-w-0 flex-1 px-4 pb-32 pt-5 sm:px-6 lg:px-8 lg:pb-10 lg:pt-8">
                <div class="{{ $wide ? '' : 'max-w-[1100px]' }}">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @include('layouts.bottom-nav')
    </body>
</html>
