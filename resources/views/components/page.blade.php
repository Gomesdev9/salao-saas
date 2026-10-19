@props(['title'])

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
            {{ $actions ?? '' }}
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-app-layout>
