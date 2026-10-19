<div class="p-6 space-y-4">
    <div>
        <x-input-label for="name" value="Nome" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $client->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="whatsapp" value="WhatsApp" />
            <x-text-input id="whatsapp" name="whatsapp" type="text" class="mt-1 block w-full" :value="old('whatsapp', $client->whatsapp)" placeholder="(00) 90000-0000" />
            <x-input-error :messages="$errors->get('whatsapp')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="birthday" value="Data de nascimento" />
            <x-text-input id="birthday" name="birthday" type="date" class="mt-1 block w-full" :value="old('birthday', $client->birthday?->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('birthday')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Observações (alergias, preferências...)" />
        <x-textarea-input id="notes" name="notes" rows="3" class="mt-1 block w-full">{{ old('notes', $client->notes) }}</x-textarea-input>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end gap-4 border-t border-gray-100 px-6 py-4">
    <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
    <x-primary-button>Salvar</x-primary-button>
</div>
