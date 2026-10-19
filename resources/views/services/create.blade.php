<x-page title="Novo serviço">
    <form method="POST" action="{{ route('services.store') }}">
        @csrf
        @include('services._form')
    </form>
</x-page>
