<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
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

        return view('dashboard', [
            'greeting' => $hour < 12 ? 'Bom dia' : ($hour < 18 ? 'Boa tarde' : 'Boa noite'),
            'firstName' => strtok($request->user()->name, ' '),
            'todayCount' => $active->count(),
            'doneCount' => $today->where('status', AppointmentStatus::Done)->count(),
            'expected' => $active->sum('price'),
            'toConfirm' => $today->where('status', AppointmentStatus::Scheduled)->count(),
            'next' => $active->where('status', '!=', AppointmentStatus::Done)
                ->filter(fn ($a) => $a->ends_at->isFuture())
                ->take(6),
            'birthdays' => $birthdays,
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
