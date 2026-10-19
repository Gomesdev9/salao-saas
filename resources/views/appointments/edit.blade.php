<x-page title="Editar agendamento">
    <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 px-6 py-4">
        <span class="text-sm text-gray-600 me-2">
            {{ $appointment->starts_at->format('d/m H:i') }}–{{ $appointment->ends_at->format('H:i') }} ·
            R$ {{ number_format($appointment->price, 2, ',', '.') }} ·
            <strong>{{ $appointment->status->label() }}</strong>
        </span>
        @foreach ($statuses as $status)
            @continue($status === $appointment->status)
            <form method="POST" action="{{ route('appointments.status', $appointment) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $status->value }}">
                <button type="submit" class="rounded-md border px-3 py-1 text-xs font-medium {{ $status->color() }}">Marcar {{ mb_strtolower($status->label()) }}</button>
            </form>
        @endforeach
        <x-input-error :messages="$errors->get('status')" class="w-full" />
    </div>

    <form method="POST" action="{{ route('appointments.update', $appointment) }}">
        @csrf
        @method('PUT')
        @include('appointments._form')
    </form>

    <div class="border-t border-gray-100 px-6 py-4 text-right">
        <x-delete-form :action="route('appointments.destroy', $appointment)" confirm="Remover este agendamento? Se o cliente só desmarcou, prefira marcar como cancelado." />
    </div>
</x-page>
