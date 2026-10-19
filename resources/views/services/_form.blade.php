<div class="p-6 space-y-4">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="name" value="Nome do serviço" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $service->name)" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="category" value="Categoria" />
            <x-text-input id="category" name="category" type="text" class="mt-1 block w-full" :value="old('category', $service->category)" placeholder="Cabelo, Unhas, Estética..." />
            <x-input-error :messages="$errors->get('category')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="duration_minutes" value="Duração (minutos)" />
            <x-text-input id="duration_minutes" name="duration_minutes" type="number" min="5" max="600" step="5" class="mt-1 block w-full" :value="old('duration_minutes', $service->duration_minutes)" required />
            <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="price" value="Preço (R$)" />
            <x-text-input id="price" name="price" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('price', $service->price)" required />
            <x-input-error :messages="$errors->get('price')" class="mt-2" />
        </div>
    </div>

    <div>
        <input type="hidden" name="active" value="0">
        <x-checkbox-input name="active" value="1" label="Ativo (disponível para agendar)" :checked="old('active', $service->active)" />
    </div>
</div>

<div class="flex items-center justify-end gap-4 border-t border-gray-100 px-6 py-4">
    <a href="{{ route('services.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
    <x-primary-button>Salvar</x-primary-button>
</div>
