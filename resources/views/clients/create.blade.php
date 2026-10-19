<x-page title="Novo cliente">
    <form method="POST" action="{{ route('clients.store') }}">
        @csrf
        @include('clients._form')
    </form>
</x-page>
