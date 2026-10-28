<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\BusinessType;
use App\Enums\PaymentMethod;
use App\Enums\Plan;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\CommissionAdvance;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Professional;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Cria um salão de demonstração com os mesmos dados do design das telas.
     * Login: dono@salao.test / senha: password
     */
    public function run(): void
    {
        $salon = Salon::create([
            'name' => 'Studio Bela Vista',
            'slug' => 'studio-bela-vista',
            'business_type' => BusinessType::BeautySalon,
            'plan' => Plan::Professional,
            'trial_ends_at' => now()->addDays(Plan::TRIAL_DAYS),
            'phone' => '(11) 99999-0000',
            'city' => 'São Paulo - SP',
            'pix_key' => 'financeiro@studiobelavista.com.br',
        ]);

        User::factory()->for($salon)->create([
            'name' => 'João Gomes',
            'email' => 'dono@salao.test',
        ]);

        // [nome, categoria, duração (min), preço, ativo]
        $services = collect([
            ['Corte feminino', 'Cabelo', 60, 90, true],
            ['Escova', 'Cabelo', 45, 60, true],
            ['Coloração', 'Cabelo', 120, 220, true],
            ['Hidratação', 'Cabelo', 60, 120, true],
            ['Manicure', 'Unhas', 45, 40, true],
            ['Pedicure', 'Unhas', 45, 45, true],
            ['Unha em gel', 'Unhas', 90, 150, false],
            ['Corte masculino', 'Barbearia', 30, 50, true],
            ['Barba', 'Barbearia', 30, 35, true],
        ])->mapWithKeys(fn (array $s) => [$s[0] => Service::create([
            'salon_id' => $salon->id,
            'name' => $s[0],
            'category' => $s[1],
            'duration_minutes' => $s[2],
            'price' => $s[3],
            'active' => $s[4],
        ])]);

        // [nome, telefone, comissão, serviços, ativo]
        $professionals = collect([
            ['Ana Souza', '(11) 98111-2233', 40, ['Corte feminino', 'Escova', 'Coloração', 'Hidratação'], true],
            ['Bruna Lima', '(11) 98222-3344', 40, ['Corte feminino', 'Escova', 'Coloração'], true],
            ['Carla Mendes', '(11) 98333-4455', 50, ['Manicure', 'Pedicure'], true],
            ['Diego Rocha', '(11) 98444-5566', 45, ['Corte masculino', 'Barba'], true],
            ['Elisa Prado', '(11) 98555-6677', 40, ['Hidratação'], false],
        ])->mapWithKeys(function (array $p) use ($salon, $services) {
            $professional = Professional::create([
                'salon_id' => $salon->id,
                'name' => $p[0],
                'phone' => $p[1],
                'commission_percent' => $p[2],
                'active' => $p[4],
            ]);
            $professional->services()->attach($services->only($p[3])->pluck('id'));

            return [$p[0] => $professional];
        });

        // [nome, WhatsApp, aniversário, observações]. Três aniversários caem nesta semana, para aparecerem no painel.
        $clients = collect([
            ['Aline Prado', '(11) 97111-2020', '1991-03-14', null],
            ['Beatriz Santos', '(11) 97222-3030', '1988-07-08', 'Cabelo com química, evitar descolorante'],
            ['Camila Torres', '(11) 97333-4040', '1995-11-30', 'Prefere horário depois das 16h'],
            ['Clara Moura', '(11) 99876-1122', today()->addDays(5)->setYear(1993)->toDateString(), null],
            ['Débora Lins', '(11) 97555-6060', '1990-09-12', null],
            ['Fernanda Reis', '(11) 97654-3210', '1987-02-03', 'Alergia a amônia. Prefere água morna.'],
            ['Juliana Alves', '(11) 97666-7070', '1992-06-21', null],
            ['Larissa Melo', '(11) 97777-8080', '1998-01-17', null],
            ['Lucas Martins', '(11) 97888-9090', '1989-08-05', null],
            ['Mariana Costa', '(11) 98765-4321', today()->addDay()->setYear(1990)->toDateString(), 'Cliente desde 2021'],
            ['Patrícia Nunes', '(11) 96111-2020', '1985-12-02', null],
            ['Paula Viana', '(11) 96222-3030', '1994-04-27', null],
            ['Pedro Henrique', '(11) 96333-4040', '1996-10-09', null],
            ['Rafael Gomes', '(11) 91234-5678', today()->addDays(3)->setYear(1991)->toDateString(), 'Degradê baixo, máquina 1'],
            ['Renata Dias', '(11) 96444-5050', '1986-05-30', null],
            ['Sônia Ribeiro', '(11) 97444-5050', '1970-05-19', 'Unhas sensíveis'],
            ['Tatiane Rocha', '(11) 96555-6060', '1999-03-08', null],
            ['Thiago Lopes', '(11) 96666-7070', '1993-07-15', null],
        ])->mapWithKeys(fn (array $c) => [$c[0] => Client::create([
            'salon_id' => $salon->id,
            'name' => $c[0],
            'whatsapp' => $c[1],
            'birthday' => $c[2],
            'notes' => $c[3],
        ])]);

        // A agenda de hoje: [profissional, horário, cliente, serviço, situação]
        $agenda = [
            ['Ana Souza', '08:30', 'Mariana Costa', 'Escova', AppointmentStatus::Done],
            ['Ana Souza', '09:30', 'Juliana Alves', 'Coloração', AppointmentStatus::Done],
            ['Ana Souza', '13:00', 'Patrícia Nunes', 'Corte feminino', AppointmentStatus::Confirmed],
            ['Ana Souza', '14:30', 'Fernanda Reis', 'Hidratação', AppointmentStatus::Scheduled],
            ['Ana Souza', '16:00', 'Camila Torres', 'Escova', AppointmentStatus::Scheduled],
            ['Bruna Lima', '09:00', 'Larissa Melo', 'Corte feminino', AppointmentStatus::NoShow],
            ['Bruna Lima', '10:30', 'Beatriz Santos', 'Coloração', AppointmentStatus::Confirmed],
            ['Bruna Lima', '14:00', 'Renata Dias', 'Escova', AppointmentStatus::Canceled],
            ['Bruna Lima', '15:00', 'Aline Prado', 'Corte feminino', AppointmentStatus::Scheduled],
            ['Carla Mendes', '08:00', 'Sônia Ribeiro', 'Manicure', AppointmentStatus::Done],
            ['Carla Mendes', '09:00', 'Paula Viana', 'Pedicure', AppointmentStatus::Done],
            ['Carla Mendes', '10:00', 'Clara Moura', 'Manicure', AppointmentStatus::Confirmed],
            ['Carla Mendes', '11:00', 'Débora Lins', 'Manicure', AppointmentStatus::Scheduled],
            ['Carla Mendes', '15:30', 'Tatiane Rocha', 'Pedicure', AppointmentStatus::Scheduled],
            ['Diego Rocha', '09:00', 'Rafael Gomes', 'Corte masculino', AppointmentStatus::Done],
            ['Diego Rocha', '10:00', 'Lucas Martins', 'Barba', AppointmentStatus::Confirmed],
            ['Diego Rocha', '11:30', 'Pedro Henrique', 'Corte masculino', AppointmentStatus::Scheduled],
            ['Diego Rocha', '17:00', 'Thiago Lopes', 'Corte masculino', AppointmentStatus::Scheduled],
        ];

        foreach ($agenda as [$professionalName, $time, $clientName, $serviceName, $status]) {
            $service = $services[$serviceName];
            $start = today()->setTimeFromTimeString($time);

            Appointment::create([
                'salon_id' => $salon->id,
                'client_id' => $clients[$clientName]->id,
                'professional_id' => $professionals[$professionalName]->id,
                'service_id' => $service->id,
                'starts_at' => $start,
                'ends_at' => $start->copy()->addMinutes($service->duration_minutes),
                'price' => $service->price,
                'status' => $status,
            ]);
        }

        $this->seedFinance($salon, $professionals, $clients);
    }

    /**
     * Financeiro de demonstração: o mês até ontem cheio de atendimentos pagos,
     * os atendimentos de hoje já pagos, despesas e vales.
     */
    private function seedFinance(Salon $salon, Collection $professionals, Collection $clients): void
    {
        mt_srand(2026); // sempre os mesmos números "aleatórios"

        // Pix é a forma mais comum, depois crédito, débito e dinheiro
        $methods = [...array_fill(0, 5, PaymentMethod::Pix), ...array_fill(0, 3, PaymentMethod::Credit), ...array_fill(0, 2, PaymentMethod::Debit), PaymentMethod::Cash];
        $pick = fn (array|Collection $items) => collect($items)->values()[mt_rand(0, count($items) - 1)];

        $pay = function (Appointment $appointment, Professional $professional, ?int $discount = null) use ($salon, $methods, $pick) {
            $discount ??= $pick([0, 0, 0, 0, 0, 0, 0, 0, 5, 10]);
            $amount = round($appointment->price * (100 - $discount) / 100, 2);

            Payment::create([
                'salon_id' => $salon->id,
                'appointment_id' => $appointment->id,
                'professional_id' => $professional->id,
                'description' => "{$appointment->client->name} — {$appointment->service->name}",
                'method' => $pick($methods),
                'subtotal' => $appointment->price,
                'discount_percent' => $discount,
                'amount' => $amount,
                'commission_percent' => $professional->commission_percent,
                'commission' => round($amount * $professional->commission_percent / 100, 2),
                'paid_at' => $appointment->ends_at,
            ]);
        };

        // Os atendimentos de hoje que já aconteceram
        Appointment::where('salon_id', $salon->id)->where('status', AppointmentStatus::Done)->with(['client', 'service'])->get()
            ->each(fn (Appointment $a) => $pay($a, $professionals->firstWhere('id', $a->professional_id), 0));

        // Do dia 1 até ontem (domingo o salão fecha)
        $slots = ['08:30', '09:30', '10:30', '11:30', '13:30', '14:30', '15:30', '16:30', '17:30'];
        $active = $professionals->where('active', true)->map(fn (Professional $p) => [$p, $p->services()->get()]);

        for ($day = today()->startOfMonth(); $day->lt(today()); $day->addDay()) {
            if ($day->isSunday()) {
                continue;
            }

            foreach ($active as [$professional, $services]) {
                foreach ($slots as $time) {
                    if (mt_rand(1, 100) > 28) {
                        continue;
                    }

                    $service = $pick($services);
                    $start = $day->copy()->setTimeFromTimeString($time);
                    $appointment = Appointment::create([
                        'salon_id' => $salon->id,
                        'client_id' => $pick($clients)->id,
                        'professional_id' => $professional->id,
                        'service_id' => $service->id,
                        'starts_at' => $start,
                        'ends_at' => $start->copy()->addMinutes($service->duration_minutes),
                        'price' => $service->price,
                        'status' => AppointmentStatus::Done,
                    ]);

                    $pay($appointment->load(['client', 'service']), $professional);
                }
            }
        }

        // Despesas do mês: as que já venceram estão pagas
        $month = today()->startOfMonth();
        foreach ([
            ['Anúncios no Instagram', 'Marketing', PaymentMethod::Credit, 420, 10],
            ['Água', 'Contas da casa', PaymentMethod::BankSlip, 180, 15],
            ['Bella Cosméticos', 'Produtos', PaymentMethod::Pix, 1640, 19],
            ['Energia elétrica', 'Contas da casa', PaymentMethod::BankSlip, 420, 20],
            ['Aluguel', 'Aluguel', PaymentMethod::BankSlip, 2800, 25],
            ['Contador', 'Serviços', PaymentMethod::BankSlip, 450, 30],
        ] as [$description, $category, $method, $amount, $dayOfMonth]) {
            $due = $month->copy()->day(min($dayOfMonth, $month->daysInMonth));

            Expense::create([
                'salon_id' => $salon->id,
                'description' => $description,
                'category' => $category,
                'method' => $method,
                'amount' => $amount,
                'due_date' => $due,
                'paid_at' => $due->lt(today()) ? $due : null,
            ]);
        }

        // Vales adiantados durante o mês
        foreach ([['Ana Souza', 300, PaymentMethod::Pix, 8], ['Carla Mendes', 200, PaymentMethod::Cash, 14], ['Diego Rocha', 150, PaymentMethod::Pix, 17]] as [$name, $amount, $method, $dayOfMonth]) {
            $date = $month->copy()->day($dayOfMonth);

            if ($date->lte(today())) {
                CommissionAdvance::create([
                    'salon_id' => $salon->id,
                    'professional_id' => $professionals[$name]->id,
                    'amount' => $amount,
                    'method' => $method,
                    'given_on' => $date,
                ]);
            }
        }
    }
}
