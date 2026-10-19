<x-page title="Editar cliente">
    <form method="POST" action="{{ route('clients.update', $client) }}">
        @csrf
        @method('PUT')
        @include('clients._form')
    </form>
</x-page>
