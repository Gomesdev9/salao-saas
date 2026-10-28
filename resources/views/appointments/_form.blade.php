<div class="p-6 space-y-4">
    @if ($clients->isEmpty())
        <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-800">
            Nenhum cliente cadastrado. <a href="{{ route('clients.create') }}" class="underline">Cadastre o cliente</a> primeiro.
        </div>
    @endif

    <div>
        <x-input-label for="client_id" value="Cliente" />
        <select id="client_id" name="client_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Selecione...</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected(old('client_id', $appointment->client_id) == $client->id)>
                    {{ $client->name }}{{ $client->whatsapp ? " — {$client->whatsapp}" : '' }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="professional_id" value="Profissional" />
            <select id="professional_id" name="professional_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Selecione...</option>
                @foreach ($professionals as $professional)
                    <option value="{{ $professional->id }}" @selected(old('professional_id', $appointment->professional_id) == $professional->id)>{{ $professional->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('professional_id')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="service_id" value="Serviço" />
            <select id="service_id" name="service_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Selecione...</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected(old('service_id', $appointment->service_id) == $service->id)>
                        {{ $service->name }} ({{ $service->duration_minutes }} min · R$ {{ number_format($service->price, 2, ',', '.') }})
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('service_id')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <x-input-label for="date" value="Data" />
            <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', $appointment->starts_at?->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('date')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="time" value="Horário" />
            <x-text-input id="time" name="time" type="time" step="300" class="mt-1 block w-full" :value="old('time', $appointment->starts_at?->format('H:i'))" required />
            <x-input-error :messages="$errors->get('time')" class="mt-2" />
        </div>
        @if ($appointment->exists)
            <div>
                <x-input-label for="status" value="Situação" />
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $appointment->status->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>
        @endif
    </div>

    <div>
        <x-input-label for="notes" value="Observações" />
        <x-textarea-input id="notes" name="notes" rows="2" class="mt-1 block w-full">{{ old('notes', $appointment->notes) }}</x-textarea-input>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>

    <p class="text-sm text-gray-500">O horário de término é calculado pela duração do serviço.</p>
</div>

<div class="flex items-center justify-end gap-4 border-t border-gray-100 px-6 py-4">
    <a href="{{ route('appointments.index', ['data' => $appointment->starts_at?->toDateString()]) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
    <x-primary-button>Salvar</x-primary-button>
</div>
