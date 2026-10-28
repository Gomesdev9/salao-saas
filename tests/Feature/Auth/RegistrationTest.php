<?php

namespace Tests\Feature\Auth;

use App\Enums\BusinessType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_owners_can_register_their_company(): void
    {
        $response = $this->post('/register', $this->validData());

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $response->assertSessionHas('status');

        $user = auth()->user();
        $this->assertSame('owner', $user->role);
        $this->assertSame('Salão da Maria', $user->salon->name);
        $this->assertSame('salao-da-maria', $user->salon->slug);
        $this->assertSame(BusinessType::AestheticsClinic, $user->salon->business_type);
        $this->assertSame('(41) 99876-5432', $user->salon->phone);
        $this->assertSame('Matinhos - PR', $user->salon->city);
    }

    public function test_company_type_and_whatsapp_are_required(): void
    {
        $response = $this->post('/register', $this->validData(['business_type' => '', 'phone' => '']));

        $response->assertSessionHasErrors(['business_type', 'phone']);
        $this->assertGuest();
    }

    public function test_company_type_must_be_one_of_the_listed_options(): void
    {
        $response = $this->post('/register', $this->validData(['business_type' => 'padaria']));

        $response->assertSessionHasErrors('business_type');
        $this->assertGuest();
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validData(array $overrides = []): array
    {
        return [
            'salon_name' => 'Salão da Maria',
            'business_type' => BusinessType::AestheticsClinic->value,
            'phone' => '(41) 99876-5432',
            'city' => 'Matinhos - PR',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            ...$overrides,
        ];
    }
}
