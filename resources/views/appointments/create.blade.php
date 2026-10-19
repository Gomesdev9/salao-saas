<x-page title="Novo agendamento">
    <form method="POST" action="{{ route('appointments.store') }}">
        @csrf
        @include('appointments._form')
    </form>
</x-page>
