<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $today = Appointment::with(['client', 'service', 'professional'])
            ->whereBetween('starts_at', [today(), today()->endOfDay()])
            ->orderBy('starts_at')
            ->get();

        $active = $today->filter(fn ($a) => in_array($a->status, AppointmentStatus::occupying()));

        // Aniversários nos próximos 7 dias
        $birthdays = Client::whereNotNull('birthday')->get()
            ->filter(fn (Client $client) => $client->hasBirthdayWithin(7))
            ->sortBy(fn (Client $client) => $client->nextBirthday())
            ->take(5);

        $hour = now()->hour;
        $salon = $request->user()->salon;

        return Inertia::render('dashboard', [
            'greeting' => $hour < 12 ? 'Bom dia' : ($hour < 18 ? 'Boa tarde' : 'Boa noite'),
            'firstName' => strtok($request->user()->name, ' '),
            'today' => ucfirst(today()->translatedFormat('l, d \\d\\e F')),
            'todayCount' => $active->count(),
            'doneCount' => $today->where('status', AppointmentStatus::Done)->count(),
            'expected' => (float) $active->sum('price'),
            'toConfirm' => $today->where('status', AppointmentStatus::Scheduled)->count(),
            'next' => $active->where('status', '!=', AppointmentStatus::Done)
                ->filter(fn ($a) => $a->ends_at->isFuture())
                ->take(6)
                ->map(fn (Appointment $appointment) => [
                    'id' => $appointment->id,
                    'time' => $appointment->starts_at->format('H:i'),
                    'client' => $appointment->client->name,
                    'service' => $appointment->service->name,
                    'professional' => strtok($appointment->professional->name, ' '),
                    'status' => $appointment->status->toProps(),
                ])
                ->values(),
            'birthdays' => $birthdays->map(fn (Client $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'whatsapp' => $client->whatsapp,
                'date' => $client->nextBirthday()->format('d/m'),
            ])->values(),
            'booking' => [
                'available' => $salon->hasOnlineBooking(),
                'url' => $salon->bookingUrl(),
                // No plano sem agendamento online, o link só funciona até o fim do teste
                'trialOnly' => ! $salon->plan->includesOnlineBooking(),
                'plan' => $salon->plan->label(),
            ],
            'professionalsCount' => Professional::where('active', true)->count(),
            'servicesCount' => Service::where('active', true)->count(),
            'clientsCount' => Client::count(),
            'firstSteps' => $this->firstSteps(),
        ]);
    }

    /**
     * Primeiros passos de uma conta nova. O quadro some do painel quando todos estiverem feitos.
     *
     * @return list<array{label: string, route: string, done: bool}>
     */
    private function firstSteps(): array
    {
        return [
            ['label' => 'Cadastre seus serviços', 'route' => 'services.create', 'done' => Service::exists()],
            ['label' => 'Cadastre os profissionais', 'route' => 'professionals.create', 'done' => Professional::exists()],
            ['label' => 'Faça o primeiro agendamento', 'route' => 'appointments.create', 'done' => Appointment::exists()],
        ];
    }
}
