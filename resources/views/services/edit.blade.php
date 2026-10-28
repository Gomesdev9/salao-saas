<x-page title="Editar serviço" :back="route('services.index')" back-label="Voltar para serviços">
    <form method="POST" action="{{ route('services.update', $service) }}" class="cartao max-w-3xl">
        @csrf
        @method('PUT')
        @include('services._form')
    </form>

    <x-remove-form id="remover" :action="route('services.destroy', $service)"
                   confirm="Remover este serviço? Os agendamentos com ele também serão apagados. Se só parou de oferecer, prefira desmarcar “Ativo”." />
</x-page>
