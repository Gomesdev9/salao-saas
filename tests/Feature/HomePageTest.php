<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Página de divulgação do sistema (rota "/").
 */
class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitors_see_the_product_page_with_the_company_signup(): void
    {
        // Sem ninguém logado, a página mostra os botões de cadastro e de entrar
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/home')
                ->where('auth', null)
                ->where('trialDays', 14));
    }

    public function test_signed_in_owners_are_offered_the_dashboard_instead_of_signup(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/home')
                ->whereNot('auth', null));
    }
}
