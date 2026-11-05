<?php

/**
 * Lista as páginas que entram na demonstração do GitHub Pages, uma URL por linha.
 *
 * Roda depois do "php artisan migrate --seed", então usa o salão de demonstração do DatabaseSeeder.
 * As páginas que aparecem como links dentro de outras (ex.: os horários do agendamento online)
 * o export.mjs encontra sozinho; aqui ficam as que as telas montam no navegador com route().
 */

use App\Enums\Plan;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Salon;
use App\Models\Service;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$salon = Salon::query()->firstOrFail();

$public = [
    route('home'),
    route('login'),
    route('register'),
    route('password.request'),
    route('booking.service', $salon),
];

$private = [
    route('dashboard'),
    route('profile.edit'),
    route('plan.edit'),
    route('appointments.index'),
    route('appointments.create'),
    route('finance.index'),
    route('finance.entries'),
    route('finance.entries', ['tipo' => 'entradas']),
    route('finance.entries', ['tipo' => 'saidas']),
    route('finance.commissions'),
    route('professionals.index'),
    route('professionals.create'),
    route('services.index'),
    route('services.create'),
    route('clients.index'),
    route('clients.create'),
];

foreach (Plan::cases() as $plan) {
    $public[] = route('register', ['plano' => $plan->value]);
}

foreach (range(-7, 21) as $offset) {
    $private[] = route('appointments.index', ['data' => today()->addDays($offset)->toDateString()]);
}

foreach (range(-5, 1) as $offset) {
    $month = today()->startOfMonth()->addMonths($offset)->format('Y-m');

    foreach (['finance.index', 'finance.entries', 'finance.commissions'] as $name) {
        $private[] = route($name, ['mes' => $month]);
    }
}

foreach (Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)->pluck('id') as $id) {
    $private[] = route('appointments.edit', $id);
    $private[] = route('appointments.payment.create', $id);
}

foreach (Professional::withoutGlobalScopes()->where('salon_id', $salon->id)->pluck('id') as $id) {
    $private[] = route('professionals.edit', $id);
}

foreach (Service::withoutGlobalScopes()->where('salon_id', $salon->id)->get() as $service) {
    $private[] = route('services.edit', $service->id);
    $public[] = route('booking.time', ['salon' => $salon, 'servico' => $service->id]);

    if ($service->category) {
        $private[] = route('services.index', ['categoria' => $service->category]);
    }
}

foreach (Client::withoutGlobalScopes()->where('salon_id', $salon->id)->pluck('id') as $id) {
    $private[] = route('clients.edit', $id);
}

echo json_encode(['public' => array_values(array_unique($public)), 'private' => array_values(array_unique($private))], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
