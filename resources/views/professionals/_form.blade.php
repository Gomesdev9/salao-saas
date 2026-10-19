<div class="p-6 space-y-4">
    <div>
        <x-input-label for="name" value="Nome" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $professional->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="phone" value="Telefone / WhatsApp" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $professional->phone)" placeholder="(00) 90000-0000" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="commission_percent" value="Comissão (%)" />
            <x-text-input id="commission_percent" name="commission_percent" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" :value="old('commission_percent', $professional->commission_percent)" required />
            <x-input-error :messages="$errors->get('commission_percent')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label value="Serviços que faz" />
        @php($selected = old('services', $professional->services->pluck('id')->all()))
        @forelse ($services as $service)
            <div class="mt-2">
                <x-checkbox-input name="services[]" :value="$service->id" :label="$service->name" :checked="in_array($service->id, $selected)" />
            </div>
        @empty
            <p class="mt-1 text-sm text-gray-500">Cadastre os serviços primeiro para poder vinculá-los.</p>
        @endforelse
        <x-input-error :messages="$errors->get('services.*')" class="mt-2" />
    </div>

    <div>
        <input type="hidden" name="active" value="0">
        <x-checkbox-input name="active" value="1" label="Ativo (aparece na agenda)" :checked="old('active', $professional->active)" />
    </div>
</div>

<div class="flex items-center justify-end gap-4 border-t border-gray-100 px-6 py-4">
    <a href="{{ route('professionals.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
    <x-primary-button>Salvar</x-primary-button>
</div>
