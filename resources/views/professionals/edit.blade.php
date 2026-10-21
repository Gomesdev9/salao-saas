<x-page title="Editar profissional" :back="route('professionals.index')" back-label="Voltar para profissionais">
    <form method="POST" action="{{ route('professionals.update', $professional) }}" class="cartao max-w-3xl">
        @csrf
        @method('PUT')
        @include('professionals._form')
    </form>

    <x-remove-form id="remover" :action="route('professionals.destroy', $professional)"
                   confirm="Remover este profissional? Os agendamentos dele também serão apagados. Se ele só parou de atender, prefira desmarcar “Ativo”." />
</x-page>
