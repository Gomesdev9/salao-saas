<x-page title="Editar profissional">
    <form method="POST" action="{{ route('professionals.update', $professional) }}">
        @csrf
        @method('PUT')
        @include('professionals._form')
    </form>
</x-page>
