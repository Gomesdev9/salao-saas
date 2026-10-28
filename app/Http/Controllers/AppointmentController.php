<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /** Horário padrão exibido na agenda (estende sozinho se houver atendimento fora dele). */
    private const DAY_START_HOUR = 8;

    private const DAY_END_HOUR = 20;

    /**
     * Agenda do dia: uma coluna por profissional no computador e uma lista (com filtro por profissional) no celular.
     */
    public function index(Request $request): View
    {
        $day = $this->parseDay($request->query('data'));

        $appointments = Appointment::with(['client', 'service', 'professional'])
            ->whereBetween('starts_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->orderBy('starts_at')
            ->get();

        // Profissionais ativos + qualquer inativo que ainda tenha horário marcado no dia
        $professionals = Professional::where('active', true)
            ->orWhereIn('id', $appointments->pluck('professional_id'))
            ->orderBy('name')
            ->get();

        $startHour = min(self::DAY_START_HOUR, $appointments->min(fn ($a) => $a->starts_at->hour) ?? self::DAY_START_HOUR);
        $endHour = max(self::DAY_END_HOUR, $appointments->max(fn ($a) => $a->ends_at->isSameDay($day) ? (int) ceil($a->ends_at->hour + $a->ends_at->minute / 60) : 24) ?? self::DAY_END_HOUR);

        return view('appointments.index', [
            'day' => $day,
            'appointments' => $appointments->groupBy('professional_id'),
            'dayList' => $appointments,
            'professionalFilter' => $professionals->firstWhere('id', $request->integer('profissional')),
            'professionals' => $professionals->load('services'),
            'startHour' => $startHour,
            'endHour' => $endHour,
            'total' => $appointments->whereIn('status', AppointmentStatus::occupying())->sum('price'),
            'count' => $appointments->whereIn('status', AppointmentStatus::occupying())->count(),
            'doneCount' => $appointments->where('status', AppointmentStatus::Done)->count(),
            'missedCount' => $appointments->whereIn('status', [AppointmentStatus::NoShow, AppointmentStatus::Canceled])->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $appointment = new Appointment([
            'professional_id' => $request->integer('profissional') ?: null,
            'starts_at' => $this->parseDay($request->query('data'))->setTimeFromTimeString($request->query('hora', '09:00')),
        ]);

        return view('appointments.create', $this->formData($appointment));
    }

    public function store(Request $request): RedirectResponse
    {
        $appointment = Appointment::create($this->validated($request));

        return redirect()
            ->route('appointments.index', ['data' => $appointment->starts_at->toDateString()])
            ->with('status', 'Horário agendado.');
    }

    public function edit(Appointment $appointment): View
    {
        return view('appointments.edit', $this->formData($appointment));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment->update($this->validated($request, $appointment));

        return redirect()
            ->route('appointments.index', ['data' => $appointment->starts_at->toDateString()])
            ->with('status', 'Agendamento atualizado.');
    }

    /**
     * Muda só o status (Confirmado, Atendido, Faltou, Cancelado).
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
        ]);

        $status = AppointmentStatus::from($data['status']);

        // Reativar um horário cancelado/faltou não pode passar por cima de outro agendamento
        if (in_array($status, AppointmentStatus::occupying()) && ! in_array($appointment->status, AppointmentStatus::occupying())) {
            $this->ensureFree($appointment->professional_id, $appointment->starts_at, $appointment->ends_at, $appointment->id, 'status');
        }

        $appointment->update(['status' => $status]);

        return back()->with('status', "Status alterado para {$status->label()}.");
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $day = $appointment->starts_at->toDateString();
        $appointment->delete();

        return redirect()->route('appointments.index', ['data' => $day])->with('status', 'Agendamento removido.');
    }

    private function formData(Appointment $appointment): array
    {
        return [
            'appointment' => $appointment,
            'clients' => Client::orderBy('name')->get(['id', 'name', 'whatsapp']),
            'professionals' => Professional::where('active', true)->orWhere('id', $appointment->professional_id)->orderBy('name')->get(),
            'services' => Service::where('active', true)->orWhere('id', $appointment->service_id)->orderBy('name')->get(),
            'statuses' => AppointmentStatus::cases(),
        ];
    }

    /**
     * Valida o formulário e devolve os dados prontos para salvar.
     */
    private function validated(Request $request, ?Appointment $current = null): array
    {
        $salonId = $request->user()->salon_id;

        $data = $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where('salon_id', $salonId)],
            'professional_id' => ['required', Rule::exists('professionals', 'id')->where('salon_id', $salonId)],
            'service_id' => ['required', Rule::exists('services', 'id')->where('salon_id', $salonId)],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $professional = Professional::findOrFail($data['professional_id']);
        $service = Service::findOrFail($data['service_id']);

        if (! $professional->services()->whereKey($service->id)->exists()) {
            throw ValidationException::withMessages([
                'service_id' => "{$professional->name} não faz o serviço {$service->name}. Ajuste no cadastro do profissional.",
            ]);
        }

        $start = Carbon::createFromFormat('Y-m-d H:i', "{$data['date']} {$data['time']}");
        $end = $start->copy()->addMinutes($service->duration_minutes);
        $status = isset($data['status']) ? AppointmentStatus::from($data['status']) : ($current?->status ?? AppointmentStatus::Scheduled);

        if (in_array($status, AppointmentStatus::occupying())) {
            $this->ensureFree($professional->id, $start, $end, $current?->id, 'time');
        }

        return [
            'client_id' => $data['client_id'],
            'professional_id' => $professional->id,
            'service_id' => $service->id,
            'starts_at' => $start,
            'ends_at' => $end,
            // Mantém o preço antigo se o serviço não mudou; senão usa o preço atual do serviço
            'price' => $current && $current->service_id === $service->id ? $current->price : $service->price,
            'status' => $status,
            'notes' => $data['notes'] ?? null,
        ];
    }

    /**
     * Impede marcar dois clientes no mesmo horário com o mesmo profissional.
     */
    private function ensureFree(int $professionalId, Carbon $start, Carbon $end, ?int $ignoreId, string $field): void
    {
        $conflict = Appointment::with('client')
            ->where('professional_id', $professionalId)
            ->overlapping($start, $end)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                $field => sprintf(
                    'Horário ocupado: %s já tem %s das %s às %s.',
                    $conflict->professional->name,
                    $conflict->client->name,
                    $conflict->starts_at->format('H:i'),
                    $conflict->ends_at->format('H:i'),
                ),
            ]);
        }
    }

    private function parseDay(?string $value): Carbon
    {
        try {
            return $value ? Carbon::createFromFormat('!Y-m-d', $value) : today();
        } catch (\Throwable) {
            return today();
        }
    }
}
