<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Professional $professional;

    private Service $service;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $salon = $this->user->salon;

        $this->service = Service::factory()->for($salon)->create(['name' => 'Escova', 'duration_minutes' => 60, 'price' => 50]);
        $this->professional = Professional::factory()->for($salon)->create(['name' => 'Ana']);
        $this->professional->services()->attach($this->service);
        $this->client = Client::factory()->for($salon)->create(['name' => 'Fernanda']);

        $this->actingAs($this->user);
    }

    private function book(array $overrides = []): TestResponse
    {
        return $this->post(route('appointments.store'), array_merge([
            'client_id' => $this->client->id,
            'professional_id' => $this->professional->id,
            'service_id' => $this->service->id,
            'date' => '2026-11-03',
            'time' => '10:00',
        ], $overrides));
    }

    public function test_agenda_pages_render(): void
    {
        $this->book();

        $this->get(route('appointments.index', ['data' => '2026-11-03']))
            ->assertOk()
            ->assertSee('Ana')
            ->assertSee('10:00 Fernanda')
            ->assertSee('R$ 50,00');

        $this->get(route('appointments.index', ['data' => 'lixo']))->assertOk();
        $this->get(route('appointments.create', ['data' => '2026-11-03', 'hora' => '14:30', 'profissional' => $this->professional->id]))
            ->assertOk()
            ->assertSee('value="14:30"', false);
        $this->get(route('appointments.edit', Appointment::first()))->assertOk();
    }

    public function test_phone_agenda_lists_the_day_and_filters_by_professional(): void
    {
        $bruna = Professional::factory()->for($this->user->salon)->create(['name' => 'Bruna Lima']);
        $bruna->services()->attach($this->service);
        $this->book();
        $this->book(['professional_id' => $bruna->id, 'time' => '11:00']);
        [$ofAna, $ofBruna] = Appointment::orderBy('starts_at')->get()->map(fn (Appointment $a) => 'href="'.route('appointments.edit', $a).'"');

        // Sem filtro: cada horário aparece na lista do celular e na coluna do computador
        $all = $this->get(route('appointments.index', ['data' => '2026-11-03']))
            ->assertOk()
            ->assertSee('Todos · 2')
            ->assertSee('Bruna · 1')
            ->assertSee('aria-controls="menu-mais"', false);
        $this->assertSame(2, substr_count($all->getContent(), $ofAna));

        // Filtrando a Bruna: o horário da Ana fica só na grade do computador
        $filtered = $this->get(route('appointments.index', ['data' => '2026-11-03', 'profissional' => $bruna->id]))->assertOk();
        $this->assertSame(1, substr_count($filtered->getContent(), $ofAna));
        $this->assertSame(2, substr_count($filtered->getContent(), $ofBruna));
    }

    public function test_booking_calculates_end_time_and_price(): void
    {
        $this->book()->assertRedirect(route('appointments.index', ['data' => '2026-11-03']));

        $appointment = Appointment::firstOrFail();
        $this->assertSame('2026-11-03 10:00', $appointment->starts_at->format('Y-m-d H:i'));
        $this->assertSame('2026-11-03 11:00', $appointment->ends_at->format('Y-m-d H:i'));
        $this->assertSame('50.00', $appointment->price);
        $this->assertSame(AppointmentStatus::Scheduled, $appointment->status);
        $this->assertSame($this->user->salon_id, $appointment->salon_id);
    }

    public function test_cannot_double_book_a_professional(): void
    {
        $this->book();

        $this->book(['time' => '10:30'])->assertSessionHasErrors(['time' => 'Horário ocupado: Ana já tem Fernanda das 10:00 às 11:00.']);
        $this->book(['time' => '09:30'])->assertSessionHasErrors('time');

        // Encostado (começa quando o outro termina) pode
        $this->book(['time' => '11:00'])->assertSessionHasNoErrors();
        $this->assertSame(2, Appointment::count());
    }

    public function test_canceled_appointment_frees_the_slot(): void
    {
        $this->book();
        $appointment = Appointment::first();

        $this->patch(route('appointments.status', $appointment), ['status' => 'canceled'])->assertSessionHasNoErrors();
        $this->book()->assertSessionHasNoErrors();

        // Reativar o cancelado agora daria conflito
        $this->patch(route('appointments.status', $appointment), ['status' => 'confirmed'])->assertSessionHasErrors('status');
        $this->assertSame(AppointmentStatus::Canceled, $appointment->fresh()->status);
    }

    public function test_professional_must_do_the_service(): void
    {
        $other = Service::factory()->for($this->user->salon)->create(['name' => 'Manicure']);

        $this->book(['service_id' => $other->id])
            ->assertSessionHasErrors(['service_id' => 'Ana não faz o serviço Manicure. Ajuste no cadastro do profissional.']);
    }

    public function test_editing_keeps_price_and_ignores_itself_in_conflict_check(): void
    {
        $this->book();
        $appointment = Appointment::first();
        $this->service->update(['price' => 80]);

        $this->put(route('appointments.update', $appointment), [
            'client_id' => $this->client->id,
            'professional_id' => $this->professional->id,
            'service_id' => $this->service->id,
            'date' => '2026-11-03',
            'time' => '10:15',
            'status' => 'confirmed',
        ])->assertSessionHasNoErrors();

        $appointment->refresh();
        $this->assertSame('10:15', $appointment->starts_at->format('H:i'));
        $this->assertSame('50.00', $appointment->price);
        $this->assertSame(AppointmentStatus::Confirmed, $appointment->status);
    }

    public function test_cannot_use_another_salons_records(): void
    {
        $foreign = Appointment::factory()->create();
        $foreignClientName = Client::withoutGlobalScopes()->find($foreign->client_id)->name;

        $this->get(route('appointments.edit', $foreign))->assertNotFound();
        $this->patch(route('appointments.status', $foreign), ['status' => 'done'])->assertNotFound();

        $this->book(['client_id' => $foreign->client_id])->assertSessionHasErrors('client_id');

        $this->get(route('appointments.index', ['data' => $foreign->starts_at->toDateString()]))
            ->assertDontSee($foreignClientName);
    }
}
