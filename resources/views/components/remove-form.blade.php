@props(['id', 'action', 'confirm' => 'Tem certeza que deseja remover?'])

{{-- Formulário escondido de remoção, disparado por um botão com form="{{ $id }}" --}}
<form id="{{ $id }}" method="POST" action="{{ $action }}" class="hidden" onsubmit="return confirm(@js($confirm))">
    @csrf
    @method('DELETE')
</form>
