<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\BusinessType;
use App\Enums\Plan;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Página de divulgação do sistema, com o convite para a empresa se cadastrar.
 */
class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('site/home', [
            'plans' => Plan::allProps(),
            'trialDays' => Plan::TRIAL_DAYS,
            'businessTypes' => array_values(array_map(
                fn (BusinessType $type) => $type->label(),
                array_filter(BusinessType::cases(), fn (BusinessType $type) => $type !== BusinessType::Other),
            )),
            // Cores reais das situações, usadas na prévia da agenda
            'statuses' => collect(AppointmentStatus::cases())->mapWithKeys(fn (AppointmentStatus $status) => [$status->value => $status->toProps()]),
            'year' => now()->year,
        ]);
    }
}
