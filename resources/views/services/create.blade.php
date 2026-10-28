<x-page title="Novo serviço" :back="route('services.index')" back-label="Voltar para serviços">
    <form method="POST" action="{{ route('services.store') }}" class="cartao max-w-3xl">
        @csrf
        @include('services._form')
    </form>
</x-page>
