<x-page title="Novo profissional" :back="route('professionals.index')" back-label="Voltar para profissionais">
    <form method="POST" action="{{ route('professionals.store') }}" class="cartao max-w-3xl">
        @csrf
        @include('professionals._form')
    </form>
</x-page>
