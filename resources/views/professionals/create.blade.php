<x-page title="Novo profissional">
    <form method="POST" action="{{ route('professionals.store') }}">
        @csrf
        @include('professionals._form')
    </form>
</x-page>
