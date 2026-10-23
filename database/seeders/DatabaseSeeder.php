<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\BusinessType;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Cria um salão de demonstração para testar o sistema.
     * Login: dono@salao.test / senha: password
     */
    public function run(): void
    {
        $salon = Salon::create([
            'name' => 'Salão Demonstração',
            'slug' => 'salao-demonstracao',
            'business_type' => BusinessType::BeautySalon,
            'phone' => '(11) 99999-0000',
            'city' => 'Minha Cidade',
        ]);

        User::factory()->for($salon)->create([
            'name' => 'Dono do Salão',
            'email' => 'dono@salao.test',
        ]);

        $services = collect([
            ['Corte feminino', 'Cabelo', 60, 70],
            ['Escova', 'Cabelo', 45, 45],
            ['Manicure', 'Unhas', 40, 30],
            ['Pedicure', 'Unhas', 45, 35],
            ['Design de sobrancelha', 'Estética', 30, 40],
        ])->map(fn ($s) => Service::create([
            'salon_id' => $salon->id,
            'name' => $s[0],
            'category' => $s[1],
            'duration_minutes' => $s[2],
            'price' => $s[3],
        ]));

        $ana = Professional::factory()->for($salon)->create(['name' => 'Ana Souza', 'commission_percent' => 40]);
        $bia = Professional::factory()->for($salon)->create(['name' => 'Bia Lima', 'commission_percent' => 50]);
        $ana->services()->attach($services->where('category', 'Cabelo')->pluck('id'));
        $bia->services()->attach($services->whereIn('category', ['Unhas', 'Estética'])->pluck('id'));

        $clients = Client::factory(15)->for($salon)->create();

        // Alguns horários marcados para hoje
        $agenda = [
            [$ana, 'Corte feminino', '09:00', AppointmentStatus::Done],
            [$ana, 'Escova', '10:30', AppointmentStatus::Confirmed],
            [$ana, 'Corte feminino', '14:00', AppointmentStatus::Scheduled],
            [$bia, 'Manicure', '09:00', AppointmentStatus::Done],
            [$bia, 'Pedicure', '10:00', AppointmentStatus::NoShow],
            [$bia, 'Design de sobrancelha', '15:30', AppointmentStatus::Scheduled],
        ];

        foreach ($agenda as $i => [$professional, $serviceName, $time, $status]) {
            $service = $services->firstWhere('name', $serviceName);
            $start = today()->setTimeFromTimeString($time);

            Appointment::create([
                'salon_id' => $salon->id,
                'client_id' => $clients[$i]->id,
                'professional_id' => $professional->id,
                'service_id' => $service->id,
                'starts_at' => $start,
                'ends_at' => $start->copy()->addMinutes($service->duration_minutes),
                'price' => $service->price,
                'status' => $status,
            ]);
        }
    }
}
