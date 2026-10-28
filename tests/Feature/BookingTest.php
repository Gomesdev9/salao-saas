<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\Plan;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Página pública em que a cliente do salão agenda sozinha.
 */
class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Salon $salon;

    private Service $service;

    private Professional $ana;

    private Professional $bruna;

    protected function setUp(): void
    {
        parent::setUp();

        // Quarta-feira, antes de o salão abrir
        Carbon::setTestNow('2026-10-21 08:00');

        $this->salon = Salon::factory()->create(['name' => 'Studio Bela', 'slug' => 'studio-bela', 'phone' => '(11) 99999-0000', 'plan' => Plan::Professional, 'trial_ends_at' => null]);
        $this->service = Service::factory()->for($this->salon)->create(['name' => 'Escova', 'category' => 'Cabelo', 'duration_minutes' => 60, 'price' => 60]);
        $this->ana = Professional::factory()->for($this->salon)->create(['name' => 'Ana Souza']);
        $this->bruna = Professional::factory()->for($this->salon)->create(['name' => 'Bruna Lima']);
        $this->ana->services()->attach($this->service);
        $this->bruna->services()->attach($this->service);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_service_page_lists_only_what_can_be_booked(): void
    {
        Service::factory()->for($this->salon)->create(['name' => 'Coloração inativa', 'active' => false])->professionals()->attach($this->ana);
        Service::factory()->for($this->salon)->create(['name' => 'Sem ninguém para fazer']);
        Service::factory()->create(['name' => 'De outro salão']);

        $this->get(route('booking.service', $this->salon))
            ->assertOk()
            ->assertSee('Studio Bela')
            ->assertSee('Escova')
            ->assertSee('60 min · R$ 60,00')
            ->assertDontSee('Coloração inativa')
            ->assertDontSee('Sem ninguém para fazer')
            ->assertDontSee('De outro salão');
    }

    public function test_time_page_shows_free_and_taken_slots(): void
    {
        $this->busy($this->ana, '10:00');

        // Com a Ana, 10:00 está ocupado
        $this->get(route('booking.time', ['salon' => $this->salon, 'servico' => $this->service->id, 'profissional' => $this->ana->id, 'data' => '2026-10-21']))
            ->assertOk()
            ->assertSee('value="09:00"', false)
            ->assertDontSee('value="10:00"', false)
            ->assertSee('Ocupado: </span>10:00', false)
            // Termina às 19h: o último horário de 1h é 18:00
            ->assertSee('value="18:00"', false)
            ->assertDontSee('value="18:30"', false);

        // Sem preferência, a Bruna está livre às 10:00
        $this->get(route('booking.time', ['salon' => $this->salon, 'servico' => $this->service->id, 'data' => '2026-10-21']))
            ->assertSee('value="10:00"', false);
    }

    public function test_closed_days_and_past_times_are_not_offered(): void
    {
        $this->get(route('booking.time', ['salon' => $this->salon, 'servico' => $this->service->id, 'data' => '2026-10-25']))
            ->assertOk()
            ->assertSee('O salão não abre neste dia');

        Carbon::setTestNow('2026-10-21 13:10');
        $this->get(route('booking.time', ['salon' => $this->salon, 'servico' => $this->service->id, 'data' => '2026-10-21']))
            ->assertDontSee('value="13:00"', false)
            ->assertSee('value="13:30"', false);
    }

    public function test_client_books_and_the_appointment_lands_in_the_agenda(): void
    {
        $response = $this->book(['name' => 'Maria Clara', 'whatsapp' => '(41) 99876-5432', 'notes' => 'Cabelo com química']);

        $client = Client::where('name', 'Maria Clara')->firstOrFail();
        $appointment = Appointment::firstOrFail();

        $response->assertRedirect(route('booking.done', [$this->salon, $client->access_token, $appointment->id]))
            ->assertCookie('cliente_salao_'.$this->salon->id);

        $this->assertSame($this->salon->id, $client->salon_id);
        $this->assertSame($this->salon->id, $appointment->salon_id);
        $this->assertSame(AppointmentStatus::Scheduled, $appointment->status);
        $this->assertSame('2026-10-21 10:00', $appointment->starts_at->format('Y-m-d H:i'));
        $this->assertSame('11:00', $appointment->ends_at->format('H:i'));
        $this->assertSame('60.00', $appointment->price);
        $this->assertStringContainsString('Cabelo com química', $appointment->notes);

        $this->get(route('booking.done', [$this->salon, $client->access_token, $appointment->id]))
            ->assertOk()
            ->assertSee('Tudo certo')
            ->assertSee('Maria')
            ->assertSee('Escova');
    }

    public function test_existing_client_is_found_by_whatsapp(): void
    {
        $client = Client::factory()->for($this->salon)->create(['name' => 'Fernanda Reis', 'whatsapp' => '(41) 99876-5432']);

        $this->book(['name' => 'Fê', 'whatsapp' => '41 998765432'])->assertRedirect();

        $this->assertSame(1, Client::count());
        $this->assertSame($client->id, Appointment::firstOrFail()->client_id);
        $this->assertSame('Fernanda Reis', $client->fresh()->name);
    }

    public function test_taken_time_is_refused(): void
    {
        $this->busy($this->ana, '10:00');

        $this->book(['profissional' => $this->ana->id])
            ->assertRedirect()
            ->assertSessionHasErrors('hora');

        $this->assertSame(1, Appointment::count());
    }

    public function test_name_and_whatsapp_are_required(): void
    {
        $this->book(['name' => '', 'whatsapp' => '1234'])->assertSessionHasErrors(['name', 'whatsapp']);

        $this->assertSame(0, Appointment::count());
    }

    public function test_client_area_lists_cancels_and_reschedules(): void
    {
        $this->book();
        $client = Client::firstOrFail();
        $appointment = Appointment::firstOrFail();

        $this->get(route('booking.mine', [$this->salon, $client->access_token]))
            ->assertOk()
            ->assertSee('Olá')
            ->assertSee('Próximo horário')
            ->assertSee('Escova');

        // Remarcar: o novo horário entra e o antigo é cancelado
        $this->withCookie('cliente_salao_'.$this->salon->id, $client->access_token)
            ->book(['hora' => '15:00', 'remarcar' => $appointment->id])
            ->assertRedirect();

        $this->assertSame(AppointmentStatus::Canceled, $appointment->fresh()->status);
        $new = Appointment::latest('id')->firstOrFail();
        $this->assertSame('15:00', $new->starts_at->format('H:i'));

        $this->patch(route('booking.cancel', [$this->salon, $client->access_token, $new->id]))
            ->assertRedirect(route('booking.mine', [$this->salon, $client->access_token]));
        $this->assertSame(AppointmentStatus::Canceled, $new->fresh()->status);
    }

    public function test_reschedule_only_cancels_the_clients_own_appointment(): void
    {
        $other = $this->busy($this->bruna, '16:00');

        $this->book(['remarcar' => $other->id])->assertRedirect();

        $this->assertSame(AppointmentStatus::Confirmed, $other->fresh()->status);
    }

    public function test_one_clients_link_does_not_open_another_clients_appointments(): void
    {
        $this->book();
        $appointment = Appointment::firstOrFail();
        Client::factory()->for($this->salon)->create(['access_token' => 'outra-chave']);

        $this->get(route('booking.done', [$this->salon, 'outra-chave', $appointment->id]))->assertNotFound();
        $this->patch(route('booking.cancel', [$this->salon, 'outra-chave', $appointment->id]))->assertNotFound();
        $this->get(route('booking.mine', [$this->salon, 'chave-errada']))->assertNotFound();

        $this->assertSame(AppointmentStatus::Scheduled, $appointment->fresh()->status);
    }

    public function test_calendar_file_has_the_appointment_time(): void
    {
        $this->book();
        $client = Client::firstOrFail();

        $this->get(route('booking.calendar', [$this->salon, $client->access_token, Appointment::firstOrFail()->id]))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->assertSee('BEGIN:VEVENT')
            ->assertSee('SUMMARY:Escova · Studio Bela');
    }

    public function test_essential_plan_after_the_trial_sends_clients_to_whatsapp(): void
    {
        $this->salon->update(['plan' => Plan::Essential, 'trial_ends_at' => now()->subDay()]);

        $this->get(route('booking.service', $this->salon))
            ->assertOk()
            ->assertSee('Chamar no WhatsApp')
            ->assertDontSee('Escova');
        $this->book();

        $this->assertSame(0, Appointment::count());

        // Durante o teste grátis funciona em qualquer plano
        $this->salon->update(['trial_ends_at' => now()->addDays(3)]);
        $this->get(route('booking.service', $this->salon))->assertSee('Escova');
    }

    public function test_owner_of_another_salon_logged_in_still_sees_this_salons_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('booking.service', $this->salon))->assertSee('Escova');
        $this->book()->assertRedirect();

        $this->assertSame($this->salon->id, Appointment::withoutGlobalScopes()->firstOrFail()->salon_id);
    }

    public function test_dashboard_shows_the_booking_link(): void
    {
        $this->actingAs(User::factory()->for($this->salon)->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('booking.service', $this->salon))
            ->assertSee('Copiar link');
    }

    private function busy(Professional $professional, string $time): Appointment
    {
        $start = today()->setTimeFromTimeString($time);

        return Appointment::factory()->for($this->salon)->create([
            'professional_id' => $professional->id,
            'service_id' => $this->service->id,
            'client_id' => Client::factory()->for($this->salon),
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
            'status' => AppointmentStatus::Confirmed,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function book(array $overrides = []): TestResponse
    {
        return $this->post(route('booking.store', $this->salon), [
            'servico' => $this->service->id,
            'data' => '2026-10-21',
            'hora' => '10:00',
            'name' => 'Maria Clara',
            'whatsapp' => '(41) 99876-5432',
            ...$overrides,
        ]);
    }
}
