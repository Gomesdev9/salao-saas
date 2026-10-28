<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Salon;
use App\Models\Service;
use App\Support\BookingSlots;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Página pública em que a cliente agenda sozinha: procedimento → profissional e horário → dados → confirmado.
 *
 * A cliente não cria senha. Ela informa nome e WhatsApp, e o sistema reaproveita o cadastro
 * do salão quando o WhatsApp já existe. Depois do primeiro agendamento, o link "Meus horários"
 * (com uma chave só dela) fica guardado num cookie deste aparelho.
 *
 * Como não há usuário logado do salão aqui, as consultas tiram o filtro automático por salão
 * e filtram pelo salão da URL.
 */
class BookingController extends Controller
{
    /** Passo 1: o procedimento. */
    public function service(Request $request, Salon $salon): View
    {
        if (! $salon->hasOnlineBooking()) {
            return $this->unavailable($salon);
        }

        $services = $this->services($salon)->get();

        return view('booking.service', [
            'salon' => $salon,
            'services' => $services,
            'categories' => $services->pluck('category')->filter()->unique()->values(),
            'selected' => $services->firstWhere('id', $request->integer('servico')),
            'reschedule' => $this->rescheduleParam($request),
            'client' => $this->rememberedClient($request, $salon),
        ]);
    }

    /** Passo 2: com quem, o dia e o horário. */
    public function time(Request $request, Salon $salon): View|RedirectResponse
    {
        if (! $salon->hasOnlineBooking()) {
            return $this->unavailable($salon);
        }

        $service = $this->services($salon)->find($request->integer('servico'));

        if (! $service) {
            return redirect()->route('booking.service', $salon);
        }

        $slots = new BookingSlots($salon, $service);
        $professionals = $slots->professionals();
        $professional = $professionals->firstWhere('id', $request->integer('profissional'));
        $day = $this->day($request->input('data')) ?? $this->firstOpenDay($salon);

        return view('booking.time', [
            'salon' => $salon,
            'service' => $service,
            'professionals' => $professionals,
            'professional' => $professional,
            'days' => BookingSlots::days(),
            'day' => $day,
            'times' => $slots->on($day, $professional),
            'chosenTime' => $request->input('hora'),
            'reschedule' => $this->rescheduleParam($request),
            'client' => $this->rememberedClient($request, $salon),
        ]);
    }

    /** Passo 3: nome e WhatsApp da cliente. */
    public function details(Request $request, Salon $salon): View|RedirectResponse
    {
        if (! $salon->hasOnlineBooking()) {
            return $this->unavailable($salon);
        }

        $choice = $this->choice($request, $salon);

        if ($choice instanceof RedirectResponse) {
            return $choice;
        }

        return view('booking.details', [
            'salon' => $salon,
            ...$choice,
            'reschedule' => $this->rescheduleParam($request),
            'client' => $this->rememberedClient($request, $salon),
        ]);
    }

    public function store(Request $request, Salon $salon): View|RedirectResponse
    {
        if (! $salon->hasOnlineBooking()) {
            return $this->unavailable($salon);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['bail', 'required', 'string', 'max:30', function (string $attribute, mixed $value, \Closure $fail) {
                if (! in_array(strlen(Phone::digits($value)), [10, 11, 12, 13])) {
                    $fail('Informe o WhatsApp com DDD, por exemplo (11) 98765-4321.');
                }
            }],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], ['name' => 'nome', 'whatsapp' => 'WhatsApp', 'notes' => 'recado']);

        $choice = $this->choice($request, $salon);

        if ($choice instanceof RedirectResponse) {
            return $choice;
        }

        $remembered = $this->rememberedClient($request, $salon);

        [$client, $appointment] = DB::transaction(function () use ($salon, $data, $choice, $remembered, $request) {
            $client = $this->findOrCreateClient($salon, $data['name'], $data['whatsapp']);
            $start = $choice['start'];

            $appointment = Appointment::create([
                'salon_id' => $salon->id,
                'client_id' => $client->id,
                'professional_id' => $choice['professionalId'],
                'service_id' => $choice['service']->id,
                'starts_at' => $start,
                'ends_at' => $start->copy()->addMinutes($choice['service']->duration_minutes),
                'price' => $choice['service']->price,
                'status' => AppointmentStatus::Scheduled,
                'notes' => trim('Agendado pela cliente no link do salão. '.($data['notes'] ?? '')),
            ]);

            // Remarcar = o novo horário entra e o antigo é cancelado (só se for dela, pelo cookie deste aparelho)
            if ($remembered?->is($client) && $old = $this->upcomingOf($client)->find($this->rescheduleParam($request))) {
                $old->update(['status' => AppointmentStatus::Canceled]);
            }

            return [$client, $appointment];
        });

        $this->remember($salon, $client);

        return redirect()->route('booking.done', [$salon, $client->accessToken(), $appointment->id]);
    }

    /** Passo 4: horário reservado. */
    public function done(Salon $salon, string $token, int $appointment): View
    {
        $client = $this->clientByToken($salon, $token);

        return view('booking.done', [
            'salon' => $salon,
            'client' => $client,
            'appointment' => $this->appointmentsOf($client)->with(['service', 'professional'])->findOrFail($appointment),
        ]);
    }

    /** Área da cliente: próximos horários e histórico. */
    public function mine(Salon $salon, string $token): View
    {
        $client = $this->clientByToken($salon, $token);
        $this->remember($salon, $client);

        $upcoming = $this->upcomingOf($client)->with(['service', 'professional'])->orderBy('starts_at')->get();

        return view('booking.mine', [
            'salon' => $salon,
            'client' => $client,
            'next' => $upcoming->first(),
            'upcoming' => $upcoming->slice(1),
            'history' => $this->appointmentsOf($client)->with('service')
                ->where(fn (Builder $q) => $q->where('starts_at', '<=', now())
                    ->orWhereNotIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed]))
                ->latest('starts_at')->limit(10)->get(),
        ]);
    }

    public function cancel(Salon $salon, string $token, int $appointment): RedirectResponse
    {
        $client = $this->clientByToken($salon, $token);
        $this->upcomingOf($client)->findOrFail($appointment)->update(['status' => AppointmentStatus::Canceled]);

        return redirect()->route('booking.mine', [$salon, $token])->with('status', 'Horário cancelado.');
    }

    /** Arquivo .ics para a cliente pôr o horário na agenda do celular. */
    public function calendar(Salon $salon, string $token, int $appointment): Response
    {
        $client = $this->clientByToken($salon, $token);
        $appointment = $this->appointmentsOf($client)->with('service')->findOrFail($appointment);
        $format = fn (Carbon $date) => $date->copy()->utc()->format('Ymd\THis\Z');
        $escape = fn (string $text) => addcslashes($text, ',;\\');

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//'.config('app.name').'//Agendamento//PT',
            'BEGIN:VEVENT',
            'UID:agendamento-'.$appointment->id.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.$format(now()),
            'DTSTART:'.$format($appointment->starts_at),
            'DTEND:'.$format($appointment->ends_at),
            'SUMMARY:'.$escape($appointment->service->name.' · '.$salon->name),
            'LOCATION:'.$escape((string) $salon->city),
            'END:VEVENT',
            'END:VCALENDAR',
        ])."\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="agendamento.ics"',
        ]);
    }

    /**
     * Confere serviço, profissional, dia e hora da URL e se o horário continua livre.
     *
     * @return array{service: Service, professional: ?Professional, professionalId: int, start: Carbon, assigned: Professional}|RedirectResponse
     */
    private function choice(Request $request, Salon $salon): array|RedirectResponse
    {
        $service = $this->services($salon)->find($request->integer('servico'));

        if (! $service) {
            return redirect()->route('booking.service', $salon);
        }

        $slots = new BookingSlots($salon, $service);
        $professionals = $slots->professionals();
        $professional = $professionals->firstWhere('id', $request->integer('profissional'));
        $day = $this->day($request->input('data'));
        $time = (string) $request->input('hora');
        $professionalId = $day ? ($slots->on($day, $professional)[$time] ?? null) : null;

        if (! $professionalId) {
            return redirect()->route('booking.time', array_filter([
                'salon' => $salon,
                'servico' => $service->id,
                'profissional' => $professional?->id,
                'data' => $day?->toDateString(),
                'remarcar' => $this->rescheduleParam($request),
            ]))->withErrors(['hora' => 'Esse horário não está mais livre. Escolha outro, por favor.']);
        }

        return [
            'service' => $service,
            'professional' => $professional,
            'professionalId' => $professionalId,
            'assigned' => $professionals->firstWhere('id', $professionalId),
            'start' => $day->copy()->setTimeFromTimeString($time),
        ];
    }

    /** Serviços ativos do salão que têm pelo menos um profissional ativo para fazer. */
    private function services(Salon $salon): Builder
    {
        return Service::withoutGlobalScope('salon')
            ->where('salon_id', $salon->id)
            ->where('active', true)
            ->whereHas('professionals', fn (Builder $q) => $q->withoutGlobalScope('salon')->where('active', true))
            ->orderBy('category')
            ->orderBy('name');
    }

    /** Reaproveita a cliente do salão com o mesmo WhatsApp; se não houver, cadastra. */
    private function findOrCreateClient(Salon $salon, string $name, string $whatsapp): Client
    {
        $digits = Phone::digits($whatsapp);

        $existing = Client::withoutGlobalScope('salon')
            ->where('salon_id', $salon->id)
            ->whereNotNull('whatsapp')
            ->get()
            ->first(fn (Client $client) => Phone::digits($client->whatsapp) === $digits);

        return $existing ?? Client::withoutGlobalScope('salon')->forceCreate([
            'salon_id' => $salon->id,
            'name' => $name,
            'whatsapp' => $whatsapp,
        ]);
    }

    private function clientByToken(Salon $salon, string $token): Client
    {
        return Client::withoutGlobalScope('salon')
            ->where('salon_id', $salon->id)
            ->where('access_token', $token)
            ->firstOrFail();
    }

    private function appointmentsOf(Client $client): Builder
    {
        return Appointment::withoutGlobalScope('salon')
            ->where('salon_id', $client->salon_id)
            ->where('client_id', $client->id);
    }

    /** Horários ainda por vir que a cliente pode remarcar ou cancelar. */
    private function upcomingOf(Client $client): Builder
    {
        return $this->appointmentsOf($client)
            ->where('starts_at', '>', now())
            ->whereIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed]);
    }

    /** A cliente que já agendou neste aparelho (cookie com a chave do "Meus horários"). */
    private function rememberedClient(Request $request, Salon $salon): ?Client
    {
        $token = $request->cookie($this->cookieName($salon));

        return is_string($token) && $token !== ''
            ? Client::withoutGlobalScope('salon')->where('salon_id', $salon->id)->where('access_token', $token)->first()
            : null;
    }

    private function remember(Salon $salon, Client $client): void
    {
        Cookie::queue($this->cookieName($salon), $client->accessToken(), 60 * 24 * 365);
    }

    private function cookieName(Salon $salon): string
    {
        return "cliente_salao_{$salon->id}";
    }

    private function rescheduleParam(Request $request): ?int
    {
        return $request->integer('remarcar') ?: null;
    }

    private function day(mixed $value): ?Carbon
    {
        try {
            $day = Carbon::createFromFormat('!Y-m-d', (string) $value);
        } catch (\Throwable) {
            return null;
        }

        return BookingSlots::isBookableDay($day) ? $day : null;
    }

    private function firstOpenDay(Salon $salon): Carbon
    {
        return collect(BookingSlots::days())->first(fn (Carbon $day) => $salon->hoursOn($day)) ?? today();
    }

    private function unavailable(Salon $salon): View
    {
        return view('booking.unavailable', ['salon' => $salon]);
    }
}
