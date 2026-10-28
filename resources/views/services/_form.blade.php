<div class="flex flex-col gap-[18px] p-6">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="name" value="Nome do serviço" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $service->name)" required autofocus />
            <x-input-error :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="category" value="Categoria" />
            <x-text-input id="category" name="category" type="text" list="categorias" :value="old('category', $service->category)" placeholder="Cabelo, Unhas, Estética..." />
            <datalist id="categorias">
                @foreach ($categories as $category)
                    <option value="{{ $category }}"></option>
                @endforeach
            </datalist>
            <x-input-error :messages="$errors->get('category')" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="duration_minutes" value="Duração (minutos)" />
            <x-text-input id="duration_minutes" name="duration_minutes" type="number" min="5" max="600" step="5" :value="old('duration_minutes', $service->duration_minutes)" required />
            <x-input-error :messages="$errors->get('duration_minutes')" />
        </div>
        <div>
            <x-input-label for="price" value="Preço (R$)" />
            <x-text-input id="price" name="price" type="number" min="0" step="0.01" :value="old('price', $service->price)" required />
            <x-input-error :messages="$errors->get('price')" />
        </div>
    </div>

    <div>
        <input type="hidden" name="active" value="0">
        <x-checkbox-input name="active" value="1" label="Ativo (disponível para agendar)" :checked="old('active', $service->active)" />
    </div>
</div>

<div class="flex flex-wrap items-center gap-3 border-t border-linha px-6 py-4">
    @if ($service->exists)
        <button type="submit" form="remover" class="mr-auto min-h-11 text-sm font-semibold text-perigo hover:underline">Remover serviço</button>
    @endif
    <a href="{{ route('services.index') }}" class="ml-auto inline-flex h-11 items-center px-4 text-sm font-semibold hover:text-cafe">Cancelar</a>
    <x-primary-button>Salvar</x-primary-button>
</div>
