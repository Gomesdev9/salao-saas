@props(['action', 'confirm' => 'Tem certeza que deseja remover?'])

<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($confirm))" class="inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-sm text-red-600 hover:text-red-800">Remover</button>
</form>
