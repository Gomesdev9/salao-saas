<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Conteúdo das telas redesenhadas (painel, profissionais, serviços, clientes).
 */
class ScreensTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-21 10:40');
        $this->user = User::factory()->create(['name' => 'João Gomes']);
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_shows_greeting_upcoming_and_birthdays(): void
    {
        $salon = $this->user->salon;
        $client = Client::factory()->for($salon)->create(['name' => 'Débora Lins', 'birthday' => '1990-10-24']);
        Client::factory()->for($salon)->create(['name' => 'Fora da Semana', 'birthday' => '1990-12-24']);

        Appointment::factory()->for($salon)->create([
            'client_id' => $client->id,
            'starts_at' => '2026-10-21 11:00', 'ends_at' => '2026-10-21 11:45',
            'price' => 40, 'status' => AppointmentStatus::Scheduled,
        ]);
        Appointment::factory()->for($salon)->create([
            'starts_at' => '2026-10-21 08:00', 'ends_at' => '2026-10-21 08:45',
            'price' => 60, 'status' => AppointmentStatus::Done,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Bom dia, João')
            ->assertSee('Quarta-feira, 21 de outubro')
            ->assertSee('R$ 100,00')
            ->assertSee('1 já atendidos · 1 a seguir')
            ->assertSee('11:00')
            ->assertSee('24/10')
            ->assertDontSee('Fora da Semana');
    }

    public function test_professionals_are_split_into_active_and_inactive(): void
    {
        Professional::factory()->for($this->user->salon)->create(['name' => 'Ana Ativa']);
        Professional::factory()->for($this->user->salon)->create(['name' => 'Elisa Inativa', 'active' => false]);

        $this->get(route('professionals.index'))
            ->assertSee('Ativos (1)')->assertSee('Inativos (1)')
            ->assertSee('Ana Ativa')->assertDontSee('Elisa Inativa');

        $this->get(route('professionals.index', ['inativos' => 1]))
            ->assertSee('Elisa Inativa')->assertDontSee('Ana Ativa');
    }

    public function test_services_are_grouped_and_filtered_by_category(): void
    {
        Service::factory()->for($this->user->salon)->create(['name' => 'Escova', 'category' => 'Cabelo', 'duration_minutes' => 120]);
        Service::factory()->for($this->user->salon)->create(['name' => 'Manicure', 'category' => 'Unhas']);

        $this->get(route('services.index'))->assertSee('Escova')->assertSee('Manicure')->assertSee('2 h');

        $this->get(route('services.index', ['categoria' => 'Unhas']))
            ->assertSee('Manicure')->assertDontSee('Escova');
    }

    public function test_client_whatsapp_link_and_birthday_badge(): void
    {
        Client::factory()->for($this->user->salon)->create([
            'name' => 'Clara Moura', 'whatsapp' => '(11) 99876-1122', 'birthday' => '1995-10-26',
        ]);

        $this->get(route('clients.index'))
            ->assertSee('https://wa.me/5511998761122')
            ->assertSee('esta semana')
            ->assertSee('1 cliente cadastrado');
    }

    public function test_next_birthday_handles_year_turn_and_leap_day(): void
    {
        $this->assertSame('2027-01-05', (new Client(['birthday' => '1990-01-05']))->nextBirthday()->toDateString());
        $this->assertSame('2027-02-28', (new Client(['birthday' => '1992-02-29']))->nextBirthday()->toDateString());
        $this->assertNull((new Client(['whatsapp' => '123']))->whatsappUrl());
    }
}
