<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Salon;
use App\Models\Service;
use App\Support\BookingSlots;
use App\Support\Money;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

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
    private const WEEKDAYS = [1 => 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];

    private const FULL_WEEKDAYS = [1 => 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];

    /** Passo 1: o procedimento. */
    public function service(Request $request, Salon $salon): InertiaResponse
    {
        if (! $salon->hasOnlineBooking()) {
            return $this->unavailable($salon);
        }

        $services = $this->services($salon)->get();

        return Inertia::render('booking/service', [
            ...$this->pageProps($request, $salon),
            // A categoria com mais procedimentos aparece primeiro
            'groups' => $services->groupBy(fn (Service $service) => $service->category ?: 'Outros')
                ->sortByDesc(fn ($items) => $items->count())
                ->map(fn ($items, string $name) => ['name' => $name, 'services' => $items->map($this->serviceProps(...))->values()])
                ->values(),
            'selectedId' => $services->firstWhere('id', $request->integer('servico'))?->id,
            'nextUrl' => route('booking.time', $salon),
            'reschedule' => $this->rescheduleParam($request),
        ]);
    }

    /** Passo 2: com quem, o dia e o horário. */
    public function time(Request $request, Salon $salon): InertiaResponse|RedirectResponse
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

        $times = $slots->on($day, $professional);
        $reschedule = $this->rescheduleParam($request);
        $base = array_filter(['salon' => $salon, 'servico' => $service->id, 'remarcar' => $reschedule]);

        return Inertia::render('booking/time', [
            ...$this->pageProps($request, $salon),
            'service' => $this->serviceProps($service),
            'changeUrl' => route('booking.service', $base),
            'anyProfessionalUrl' => route('booking.time', [...$base, 'data' => $day->toDateString()]),
            'professional' => $professional ? ['id' => $professional->id, 'name' => $professional->name, 'firstName' => strtok($professional->name, ' ')] : null,
            'professionals' => $professionals->map(fn (Professional $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'firstName' => strtok($item->name, ' '),
                'url' => route('booking.time', [...$base, 'profissional' => $item->id, 'data' => $day->toDateString()]),
            ])->values(),
            'month' => ucfirst($day->translatedFormat('F \\d\\e Y')),
            'days' => array_map(fn (Carbon $option) => [
                'date' => $option->toDateString(),
                'weekday' => self::WEEKDAYS[$option->dayOfWeekIso],
                'number' => $option->day,
                'open' => (bool) $salon->hoursOn($option),
                'url' => route('booking.time', array_filter([...$base, 'profissional' => $professional?->id, 'data' => $option->toDateString()])),
            ], BookingSlots::days()),
            'day' => $day->toDateString(),
            'dayLabel' => self::WEEKDAYS[$day->dayOfWeekIso].', '.$day->translatedFormat('j \\d\\e M'),
            'isOpen' => (bool) $salon->hoursOn($day),
            'hoursSummary' => $salon->openingHoursSummary(),
            'periods' => collect($times)
                ->map(fn (?int $professionalId, string $time) => ['time' => $time, 'free' => (bool) $professionalId])
                ->groupBy(fn (array $slot) => $slot['time'] < '12:00' ? 'Manhã' : ($slot['time'] < '18:00' ? 'Tarde' : 'Noite'))
                ->map(fn ($items, string $name) => ['name' => $name, 'slots' => $items->values()])
                ->values(),
            'chosenTime' => $request->input('hora'),
            'nextUrl' => route('booking.details', $salon),
            'params' => array_filter(['servico' => $service->id, 'profissional' => $professional?->id, 'data' => $day->toDateString(), 'remarcar' => $reschedule]),
        ]);
    }

    /** Passo 3: nome e WhatsApp da cliente. */
    public function details(Request $request, Salon $salon): InertiaResponse|RedirectResponse
    {
        if (! $salon->hasOnlineBooking()) {
            return $this->unavailable($salon);
        }

        $choice = $this->choice($request, $salon);

        if ($choice instanceof RedirectResponse) {
            return $choice;
        }

        $client = $this->rememberedClient($request, $salon);
        $start = $choice['start'];
        $reschedule = $this->rescheduleParam($request);
        $params = array_filter([
            'servico' => $choice['service']->id,
            'profissional' => $choice['professional']?->id,
            'data' => $start->toDateString(),
            'hora' => $start->format('H:i'),
            'remarcar' => $reschedule,
        ]);

        return Inertia::render('booking/details', [
            ...$this->pageProps($request, $salon),
            'service' => $this->serviceProps($choice['service']),
            'params' => $params,
            'storeUrl' => route('booking.store', $salon),
            'backUrl' => route('booking.time', ['salon' => $salon, ...$params]),
            'dateLabel' => ucfirst($start->translatedFormat('l, j \\d\\e F')),
            'time' => $start->format('H:i'),
            'professionalName' => $choice['assigned']->name,
            'prefill' => ['name' => $client?->name ?? '', 'whatsapp' => $client?->whatsapp ?? ''],
            'replacesOld' => $reschedule && $client,
        ]);
    }

    public function store(Request $request, Salon $salon): InertiaResponse|RedirectResponse
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
    public function done(Salon $salon, string $token, int $appointment): InertiaResponse
    {
        $client = $this->clientByToken($salon, $token);
        $appointment = $this->appointmentsOf($client)->with(['service', 'professional'])->findOrFail($appointment);
        $start = $appointment->starts_at;

        return Inertia::render('booking/done', [
            'salon' => $this->salonProps($salon),
            'firstName' => strtok($client->name, ' '),
            'appointment' => [
                'date' => self::WEEKDAYS[$start->dayOfWeekIso].', '.$start->translatedFormat('j \\d\\e M'),
                'time' => $start->format('H:i'),
                'service' => $appointment->service->name,
                'details' => $appointment->service->duration_minutes.' min · '.Money::format($appointment->price),
                'professional' => $appointment->professional->name,
                'status' => $appointment->status->toProps(),
            ],
            'calendarUrl' => route('booking.calendar', [$salon, $client->access_token, $appointment->id]),
            'mineUrl' => route('booking.mine', [$salon, $client->access_token]),
        ]);
    }

    /** Área da cliente: próximos horários e histórico. */
    public function mine(Salon $salon, string $token): InertiaResponse
    {
        $client = $this->clientByToken($salon, $token);
        $this->remember($salon, $client);

        $upcoming = $this->upcomingOf($client)->with(['service', 'professional'])->orderBy('starts_at')->get()
            ->map(fn (Appointment $appointment) => [
                'id' => $appointment->id,
                'service' => $appointment->service->name,
                'month' => $appointment->starts_at->translatedFormat('M'),
                'day' => $appointment->starts_at->format('d'),
                'weekday' => self::FULL_WEEKDAYS[$appointment->starts_at->dayOfWeekIso],
                'time' => $appointment->starts_at->format('H:i'),
                'duration' => $appointment->service->duration_minutes,
                'professional' => $appointment->professional->name,
                'status' => $appointment->status->toProps(),
                'rescheduleUrl' => route('booking.time', [
                    'salon' => $salon,
                    'servico' => $appointment->service_id,
                    'profissional' => $appointment->professional_id,
                    'data' => $appointment->starts_at->toDateString(),
                    'remarcar' => $appointment->id,
                ]),
                'cancelUrl' => route('booking.cancel', [$salon, $token, $appointment->id]),
            ]);

        return Inertia::render('booking/mine', [
            'salon' => $this->salonProps($salon),
            'firstName' => strtok($client->name, ' '),
            'next' => $upcoming->first(),
            'upcoming' => $upcoming->slice(1)->values(),
            'history' => $this->appointmentsOf($client)->with('service')
                ->where(fn (Builder $q) => $q->where('starts_at', '<=', now())
                    ->orWhereNotIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed]))
                ->latest('starts_at')->limit(10)->get()
                ->map(fn (Appointment $appointment) => [
                    'id' => $appointment->id,
                    'service' => $appointment->service->name,
                    'date' => $appointment->starts_at->translatedFormat('j M Y'),
                    'status' => $appointment->status->toProps(),
                    'againUrl' => route('booking.time', ['salon' => $salon, 'servico' => $appointment->service_id]),
                ]),
            'newUrl' => route('booking.service', $salon),
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

    private function unavailable(Salon $salon): InertiaResponse
    {
        return Inertia::render('booking/unavailable', ['salon' => $this->salonProps($salon)]);
    }

    /**
     * O que as telas dos passos usam: o salão e o link "Meus horários" quando a cliente já agendou neste aparelho.
     *
     * @return array{salon: array{name: string, city: ?string, whatsappUrl: ?string}, accountUrl: ?string}
     */
    private function pageProps(Request $request, Salon $salon): array
    {
        $client = $this->rememberedClient($request, $salon);

        return [
            'salon' => $this->salonProps($salon),
            'accountUrl' => $client ? route('booking.mine', [$salon, $client->access_token]) : null,
        ];
    }

    /**
     * @return array{name: string, city: ?string, whatsappUrl: ?string}
     */
    private function salonProps(Salon $salon): array
    {
        return ['name' => $salon->name, 'city' => $salon->city, 'whatsappUrl' => $salon->whatsappUrl()];
    }

    /**
     * @return array{id: int, name: string, details: string, price: string}
     */
    private function serviceProps(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'details' => $service->duration_minutes.' min · '.Money::format($service->price),
            'price' => Money::format($service->price),
        ];
    }
}
