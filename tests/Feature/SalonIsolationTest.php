<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Garante que um salão nunca enxerga nem altera os dados de outro.
 */
class SalonIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_only_show_records_from_own_salon(): void
    {
        $maria = User::factory()->create();
        $joao = User::factory()->create();

        Client::factory()->for($maria->salon)->create(['name' => 'Cliente da Maria']);
        Client::factory()->for($joao->salon)->create(['name' => 'Cliente do João']);

        $this->actingAs($maria)
            ->get(route('clients.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('clients/index')
                ->has('clients.data', 1)
                ->where('clients.data.0.name', 'Cliente da Maria'));
    }

    public function test_cannot_open_or_change_records_from_another_salon(): void
    {
        $maria = User::factory()->create();
        $joao = User::factory()->create();

        $client = Client::factory()->for($joao->salon)->create();
        $service = Service::factory()->for($joao->salon)->create();
        $professional = Professional::factory()->for($joao->salon)->create();

        $this->actingAs($maria);

        $this->get(route('clients.edit', $client))->assertNotFound();
        $this->put(route('services.update', $service), ['name' => 'Hack', 'duration_minutes' => 30, 'price' => 1])->assertNotFound();
        $this->delete(route('professionals.destroy', $professional))->assertNotFound();

        $this->assertModelExists($professional);
    }

    public function test_new_records_are_saved_in_the_logged_in_salon(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('clients.store'), ['name' => 'Fernanda']);

        $this->assertDatabaseHas('clients', ['name' => 'Fernanda', 'salon_id' => $user->salon_id]);
    }

    public function test_cannot_link_a_professional_to_another_salons_service(): void
    {
        $maria = User::factory()->create();
        $foreignService = Service::factory()->create();

        $this->actingAs($maria)
            ->post(route('professionals.store'), [
                'name' => 'Ana',
                'commission_percent' => 40,
                'services' => [$foreignService->id],
            ])
            ->assertSessionHasErrors('services.0');

        $this->assertDatabaseMissing('professionals', ['name' => 'Ana']);
    }
}
