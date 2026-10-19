<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalonRecordsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_pages_render(): void
    {
        foreach (['dashboard', 'professionals.index', 'professionals.create', 'services.index', 'services.create', 'clients.index', 'clients.create'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_service_crud(): void
    {
        $this->post(route('services.store'), [
            'name' => 'Escova', 'category' => 'Cabelo', 'duration_minutes' => 45, 'price' => '49.90', 'active' => '1',
        ])->assertRedirect(route('services.index'));

        $service = Service::firstOrFail();
        $this->assertSame('49.90', $service->price);
        $this->get(route('services.index'))->assertSee('R$ 49,90');

        $this->put(route('services.update', $service), [
            'name' => 'Escova longa', 'duration_minutes' => 60, 'price' => 60, 'active' => '0',
        ])->assertRedirect(route('services.index'));
        $this->assertFalse($service->fresh()->active);

        $this->get(route('services.edit', $service))->assertOk()->assertSee('Escova longa');

        $this->delete(route('services.destroy', $service));
        $this->assertModelMissing($service);
    }

    public function test_professional_with_services(): void
    {
        $services = Service::factory(2)->for($this->user->salon)->create();

        $this->post(route('professionals.store'), [
            'name' => 'Ana', 'commission_percent' => 40, 'active' => '1', 'services' => $services->pluck('id')->all(),
        ])->assertRedirect(route('professionals.index'));

        $professional = Professional::firstOrFail();
        $this->assertCount(2, $professional->services);

        $this->put(route('professionals.update', $professional), [
            'name' => 'Ana Souza', 'commission_percent' => 50, 'active' => '1', 'services' => [$services[0]->id],
        ]);
        $this->assertCount(1, $professional->fresh()->services);
        $this->get(route('professionals.edit', $professional))->assertOk();
    }

    public function test_commission_must_be_between_0_and_100(): void
    {
        $this->post(route('professionals.store'), ['name' => 'Ana', 'commission_percent' => 120])
            ->assertSessionHasErrors(['commission_percent' => 'O campo comissão não pode ser maior que 100.']);
    }

    public function test_client_search(): void
    {
        Client::factory()->for($this->user->salon)->create(['name' => 'Juliana Prado']);
        Client::factory()->for($this->user->salon)->create(['name' => 'Marcos Lima']);

        $this->get(route('clients.index', ['busca' => 'julia']))
            ->assertSee('Juliana Prado')
            ->assertDontSee('Marcos Lima');

        $this->get(route('clients.edit', Client::first()))->assertOk();
    }
}
