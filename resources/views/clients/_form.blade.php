<div class="flex flex-col gap-[18px] p-6">
    <div>
        <x-input-label for="name" value="Nome" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $client->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="whatsapp" value="WhatsApp" />
            <x-text-input id="whatsapp" name="whatsapp" type="tel" :value="old('whatsapp', $client->whatsapp)" placeholder="(00) 90000-0000" />
            <x-input-error :messages="$errors->get('whatsapp')" />
        </div>
        <div>
            <x-input-label for="birthday" value="Data de nascimento" />
            <x-text-input id="birthday" name="birthday" type="date" :value="old('birthday', $client->birthday?->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('birthday')" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Observações (alergias, preferências...)" />
        <x-textarea-input id="notes" name="notes" rows="3">{{ old('notes', $client->notes) }}</x-textarea-input>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>

<div class="flex flex-wrap items-center gap-3 border-t border-linha px-6 py-4">
    @if ($client->exists)
        <button type="submit" form="remover" class="mr-auto min-h-11 text-sm font-semibold text-perigo hover:underline">Remover cliente</button>
    @endif
    <a href="{{ route('clients.index') }}" class="ml-auto inline-flex h-11 items-center px-4 text-sm font-semibold hover:text-cafe">Cancelar</a>
    <x-primary-button>Salvar</x-primary-button>
</div>
