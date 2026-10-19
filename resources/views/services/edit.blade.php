<x-page title="Editar serviço">
    <form method="POST" action="{{ route('services.update', $service) }}">
        @csrf
        @method('PUT')
        @include('services._form')
    </form>
</x-page>
