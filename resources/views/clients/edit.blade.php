<x-page title="Editar cliente" :back="route('clients.index')" back-label="Voltar para clientes">
    <form method="POST" action="{{ route('clients.update', $client) }}" class="cartao max-w-3xl">
        @csrf
        @method('PUT')
        @include('clients._form')
    </form>

    <x-remove-form id="remover" :action="route('clients.destroy', $client)" confirm="Remover este cliente? Os agendamentos dele também serão apagados." />
</x-page>
