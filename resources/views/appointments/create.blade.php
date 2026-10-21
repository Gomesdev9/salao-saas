<x-page title="Novo agendamento" :back="route('appointments.index', ['data' => $appointment->starts_at->toDateString()])" :back-label="'Voltar para a agenda de '.$appointment->starts_at->format('d/m')">
    <form method="POST" action="{{ route('appointments.store') }}" class="cartao max-w-3xl">
        @csrf
        @include('appointments._form')
    </form>
</x-page>
