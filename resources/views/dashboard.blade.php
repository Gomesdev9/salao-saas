<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ Auth::user()->salon->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('appointments.index') }}" class="block bg-white shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                <div class="text-3xl font-semibold text-gray-900">{{ $todayCount }}</div>
                <div class="mt-1 text-gray-600">Atendimentos hoje</div>
            </a>
            <a href="{{ route('professionals.index') }}" class="block bg-white shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                <div class="text-3xl font-semibold text-gray-900">{{ $professionalsCount }}</div>
                <div class="mt-1 text-gray-600">Profissionais ativos</div>
            </a>
            <a href="{{ route('services.index') }}" class="block bg-white shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                <div class="text-3xl font-semibold text-gray-900">{{ $servicesCount }}</div>
                <div class="mt-1 text-gray-600">Serviços ativos</div>
            </a>
            <a href="{{ route('clients.index') }}" class="block bg-white shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                <div class="text-3xl font-semibold text-gray-900">{{ $clientsCount }}</div>
                <div class="mt-1 text-gray-600">Clientes</div>
            </a>
        </div>
    </div>
</x-app-layout>
