<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Salon;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addDay()->setTime(10, 0);

        return [
            'salon_id' => Salon::factory(),
            'client_id' => fn (array $a) => Client::factory()->create(['salon_id' => $a['salon_id']]),
            'professional_id' => fn (array $a) => Professional::factory()->create(['salon_id' => $a['salon_id']]),
            'service_id' => fn (array $a) => Service::factory()->create(['salon_id' => $a['salon_id']]),
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
            'price' => 50,
            'status' => AppointmentStatus::Scheduled,
        ];
    }
}
