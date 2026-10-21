<div class="flex flex-col gap-[18px] p-6">
    <div>
        <x-input-label for="name" value="Nome" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $professional->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="phone" value="Telefone / WhatsApp" />
            <x-text-input id="phone" name="phone" type="tel" :value="old('phone', $professional->phone)" placeholder="(00) 90000-0000" />
            <x-input-error :messages="$errors->get('phone')" />
        </div>
        <div>
            <x-input-label for="commission_percent" value="Comissão (%)" />
            <x-text-input id="commission_percent" name="commission_percent" type="number" min="0" max="100" step="0.01" :value="old('commission_percent', $professional->commission_percent)" required />
            <x-input-error :messages="$errors->get('commission_percent')" />
        </div>
    </div>

    <fieldset>
        <legend class="mb-1 text-sm font-semibold">Serviços que faz</legend>
        @php($selected = old('services', $professional->services->pluck('id')->all()))
        <div class="grid gap-x-4 sm:grid-cols-2">
            @forelse ($services as $service)
                <x-checkbox-input name="services[]" :value="$service->id" :label="$service->name" :checked="in_array($service->id, $selected)" />
            @empty
                <p class="text-sm text-suave">Cadastre os serviços primeiro para poder vinculá-los.</p>
            @endforelse
        </div>
        <x-input-error :messages="$errors->get('services.*')" />
    </fieldset>

    <div>
        <input type="hidden" name="active" value="0">
        <x-checkbox-input name="active" value="1" label="Ativo (aparece na agenda)" :checked="old('active', $professional->active)" />
    </div>
</div>

<div class="flex flex-wrap items-center gap-3 border-t border-linha px-6 py-4">
    @if ($professional->exists)
        <button type="submit" form="remover" class="mr-auto min-h-11 text-sm font-semibold text-perigo hover:underline">Remover profissional</button>
    @endif
    <a href="{{ route('professionals.index') }}" class="ml-auto inline-flex h-11 items-center px-4 text-sm font-semibold hover:text-cafe">Cancelar</a>
    <x-primary-button>Salvar</x-primary-button>
</div>
