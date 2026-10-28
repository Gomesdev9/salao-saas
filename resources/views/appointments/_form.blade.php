{{-- Campos do agendamento. Usado em novo e editar. --}}
<div class="flex flex-col gap-[18px] p-6">
    @if ($clients->isEmpty())
        <div class="rounded-campo bg-mel px-4 py-3 text-sm text-mel-texto">
            Nenhum cliente cadastrado. <a href="{{ route('clients.create') }}" class="font-semibold underline">Cadastre o cliente</a> primeiro.
        </div>
    @endif

    <div>
        <x-input-label for="client_id" value="Cliente" />
        <x-select-input id="client_id" name="client_id" required>
            <option value="">Selecione...</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected(old('client_id', $appointment->client_id) == $client->id)>
                    {{ $client->name }}{{ $client->whatsapp ? " — {$client->whatsapp}" : '' }}
                </option>
            @endforeach
        </x-select-input>
        <x-input-error :messages="$errors->get('client_id')" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="professional_id" value="Profissional" />
            <x-select-input id="professional_id" name="professional_id" required>
                <option value="">Selecione...</option>
                @foreach ($professionals as $professional)
                    <option value="{{ $professional->id }}" @selected(old('professional_id', $appointment->professional_id) == $professional->id)>{{ $professional->name }}</option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('professional_id')" />
        </div>
        <div>
            <x-input-label for="service_id" value="Serviço" />
            <x-select-input id="service_id" name="service_id" required>
                <option value="">Selecione...</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected(old('service_id', $appointment->service_id) == $service->id)>
                        {{ $service->name }} ({{ $service->duration_minutes }} min · R$ {{ number_format($service->price, 2, ',', '.') }})
                    </option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('service_id')" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <x-input-label for="date" value="Data" />
            <x-text-input id="date" name="date" type="date" :value="old('date', $appointment->starts_at?->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('date')" />
        </div>
        <div>
            <x-input-label for="time" value="Horário" />
            <x-text-input id="time" name="time" type="time" step="300" :value="old('time', $appointment->starts_at?->format('H:i'))" required />
            <x-input-error :messages="$errors->get('time')" />
        </div>
        <div>
            <span class="mb-1.5 block text-sm font-semibold">Término</span>
            <span class="flex h-11 items-center rounded-campo bg-creme px-3 text-[15px] text-suave">
                {{ $appointment->exists ? $appointment->ends_at->format('H:i').' (pela duração)' : 'Pela duração do serviço' }}
            </span>
        </div>
    </div>

    @if ($appointment->exists)
        <div class="sm:w-1/3">
            <x-input-label for="status" value="Situação" />
            <x-select-input id="status" name="status">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $appointment->status->value) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('status')" />
        </div>
    @endif

    <div>
        <x-input-label for="notes" value="Observações" />
        <x-textarea-input id="notes" name="notes" rows="3">{{ old('notes', $appointment->notes) }}</x-textarea-input>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>

<div class="flex flex-wrap items-center gap-3 border-t border-linha px-6 py-4">
    @if ($appointment->exists)
        <button type="submit" form="remover-agendamento" class="mr-auto min-h-11 text-sm font-semibold text-perigo hover:underline">Remover agendamento</button>
    @endif
    <a href="{{ route('appointments.index', ['data' => $appointment->starts_at?->toDateString()]) }}" class="ml-auto inline-flex h-11 items-center px-4 text-sm font-semibold hover:text-cafe">Cancelar</a>
    <x-primary-button>Salvar</x-primary-button>
</div>
