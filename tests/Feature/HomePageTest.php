<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Página de divulgação do sistema (rota "/").
 */
class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitors_see_the_product_page_with_the_company_signup(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Cadastrar minha empresa')
            ->assertSee(route('register'))
            ->assertSee(route('login'));
    }

    public function test_signed_in_owners_are_offered_the_dashboard_instead_of_signup(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('Ir para o painel')
            ->assertSee(route('dashboard'))
            ->assertDontSee('Cadastrar minha empresa');
    }
}
