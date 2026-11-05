<?php

namespace Tests\Feature;

use App\Enums\Plan;
use App\Models\Professional;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->for(Salon::factory()->state(['plan' => Plan::Essential]))->create();
        $this->actingAs($this->user);
    }

    public function test_home_page_shows_the_three_plans(): void
    {
        auth()->logout();

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('plans.0.label', 'Essencial')->where('plans.0.formattedPrice', 'R$ 49,90')
                ->where('plans.1.label', 'Profissional')->where('plans.1.formattedPrice', 'R$ 89,90')->where('plans.1.highlighted', true)
                ->where('plans.2.label', 'Premium')->where('plans.2.formattedPrice', 'R$ 149,90'));
    }

    public function test_plan_page_shows_current_plan_trial_and_usage(): void
    {
        Professional::factory()->for($this->user->salon)->create(['active' => true]);

        $this->get(route('plan.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('plan/edit')
                ->where('current', 'essencial')
                ->where('trial.daysLeft', 14)
                ->where('activeProfessionals', 1)
                ->where('plans.0.formattedPrice', 'R$ 49,90')
                ->where('plans.0.professionalLimit', 2)
                ->where('plans.2.label', 'Premium'));
    }

    public function test_owner_can_change_plan(): void
    {
        $this->patch(route('plan.update'), ['plan' => 'premium'])
            ->assertRedirect(route('plan.edit'))
            ->assertSessionHas('status');

        $this->assertSame(Plan::Premium, $this->user->salon->fresh()->plan);
    }

    public function test_cannot_move_to_a_plan_smaller_than_the_active_team(): void
    {
        $this->user->salon->update(['plan' => Plan::Professional]);
        Professional::factory()->for($this->user->salon)->count(3)->create(['active' => true]);
        Professional::factory()->for($this->user->salon)->create(['active' => false]);

        $this->patch(route('plan.update'), ['plan' => 'essencial'])->assertSessionHasErrors('plan');
        $this->assertSame(Plan::Professional, $this->user->salon->fresh()->plan);
    }

    public function test_essential_plan_allows_two_active_professionals(): void
    {
        $this->post(route('professionals.store'), ['name' => 'Ana', 'commission_percent' => 40, 'active' => 1])->assertSessionHasNoErrors();
        $this->post(route('professionals.store'), ['name' => 'Bruna', 'commission_percent' => 40, 'active' => 1])->assertSessionHasNoErrors();

        // O terceiro ativo passa do limite; como inativo pode
        $this->post(route('professionals.store'), ['name' => 'Carla', 'commission_percent' => 40, 'active' => 1])->assertSessionHasErrors('active');
        $this->post(route('professionals.store'), ['name' => 'Carla', 'commission_percent' => 40, 'active' => 0])->assertSessionHasNoErrors();

        $carla = Professional::where('name', 'Carla')->first();
        $this->assertFalse($carla->active);

        // Ativar a Carla depois também esbarra no limite, mas editar quem já é ativo continua livre
        $this->put(route('professionals.update', $carla), ['name' => 'Carla', 'commission_percent' => 40, 'active' => 1])->assertSessionHasErrors('active');
        $ana = Professional::where('name', 'Ana')->first();
        $this->put(route('professionals.update', $ana), ['name' => 'Ana Souza', 'commission_percent' => 45, 'active' => 1])->assertSessionHasNoErrors();

        $this->get(route('professionals.create'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('canActivate', false)
            ->where('planLimit', 'O plano Essencial permite até 2 profissionais ativos, e a sua equipe já chegou nesse número.'));
    }

    public function test_other_companies_professionals_do_not_count_for_the_limit(): void
    {
        Professional::factory()->for(Salon::factory())->count(3)->create(['active' => true]);

        $this->post(route('professionals.store'), ['name' => 'Ana', 'commission_percent' => 40, 'active' => 1])->assertSessionHasNoErrors();
        $this->assertTrue($this->user->salon->canActivateProfessional());
    }
}
