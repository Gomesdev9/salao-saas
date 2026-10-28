<x-page title="Novo cliente" :back="route('clients.index')" back-label="Voltar para clientes">
    <form method="POST" action="{{ route('clients.store') }}" class="cartao max-w-3xl">
        @csrf
        @include('clients._form')
    </form>
</x-page>
