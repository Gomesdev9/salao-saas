<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentMethod;
use App\Models\Appointment;
use App\Models\CommissionAdvance;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Professional;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Financeiro: receber pagamento, lançamentos, visão do mês e comissões.
 */
class FinanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-21 15:00');
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function appointment(array $attributes = []): Appointment
    {
        $professional = Professional::factory()->for($this->user->salon)->create(['name' => 'Ana Souza', 'commission_percent' => 40]);

        return Appointment::factory()->for($this->user->salon)->create([
            'professional_id' => $professional->id,
            'starts_at' => '2026-10-21 14:00',
            'ends_at' => '2026-10-21 15:00',
            'price' => 120,
            ...$attributes,
        ]);
    }

    public function test_receiving_payment_applies_discount_commission_and_marks_done(): void
    {
        $appointment = $this->appointment();

        $this->get(route('appointments.payment.create', $appointment))
            ->assertOk()
            ->assertSee('Receber pagamento')
            ->assertSee('Comissão de Ana Souza (40%)');

        $this->post(route('appointments.payment.store', $appointment), [
            'method' => 'cash',
            'discount_percent' => 10,
            'cash_received' => '150,00',
        ])->assertRedirect(route('appointments.index', ['data' => '2026-10-21']));

        $payment = $appointment->fresh()->payment;
        $this->assertSame(AppointmentStatus::Done, $appointment->fresh()->status);
        $this->assertSame(PaymentMethod::Cash, $payment->method);
        $this->assertEquals(108, $payment->amount);
        $this->assertEquals(43.2, $payment->commission);
        $this->assertEquals(42, $payment->change());
        $this->assertSame("{$appointment->client->name} — {$appointment->service->name}", $payment->description);

        $this->get(route('appointments.edit', $appointment))
            ->assertSee('Pago')
            ->assertSee('troco de R$ 42,00')
            ->assertDontSee('Receber pagamento');
    }

    public function test_cash_received_must_cover_the_total(): void
    {
        $appointment = $this->appointment();

        $this->post(route('appointments.payment.store', $appointment), [
            'method' => 'cash', 'discount_percent' => 0, 'cash_received' => '100',
        ])->assertSessionHasErrors('cash_received');

        $this->assertNull($appointment->fresh()->payment);
    }

    public function test_cannot_pay_twice_or_pay_a_canceled_appointment(): void
    {
        $paid = $this->appointment();
        Payment::factory()->for($this->user->salon)->create(['appointment_id' => $paid->id]);
        $canceled = Appointment::factory()->for($this->user->salon)->create(['status' => AppointmentStatus::Canceled]);

        $this->post(route('appointments.payment.store', $paid), ['method' => 'pix', 'discount_percent' => 0])
            ->assertSessionHasErrors('payment');
        $this->get(route('appointments.payment.create', $canceled))
            ->assertRedirect(route('appointments.edit', $canceled));

        $this->assertSame(1, Payment::count());
    }

    public function test_paid_appointment_cannot_be_deleted_until_payment_is_undone(): void
    {
        $appointment = $this->appointment();
        Payment::factory()->for($this->user->salon)->create(['appointment_id' => $appointment->id]);

        $this->delete(route('appointments.destroy', $appointment))->assertSessionHasErrors('payment');
        $this->assertModelExists($appointment);

        $this->delete(route('appointments.payment.destroy', $appointment));
        $this->assertSame(0, Payment::count());

        $this->delete(route('appointments.destroy', $appointment));
        $this->assertModelMissing($appointment);
    }

    public function test_month_overview_sums_received_expenses_commissions_and_profit(): void
    {
        $salon = $this->user->salon;
        Payment::factory()->for($salon)->create(['amount' => 300, 'commission' => 120, 'method' => PaymentMethod::Pix, 'paid_at' => '2026-10-05 10:00']);
        Payment::factory()->for($salon)->create(['amount' => 100, 'commission' => 40, 'method' => PaymentMethod::Credit, 'paid_at' => '2026-10-21 11:00']);
        Payment::factory()->for($salon)->create(['amount' => 999, 'paid_at' => '2026-09-30 18:00']); // mês anterior
        Payment::factory()->create(['amount' => 777, 'paid_at' => '2026-10-10 10:00']); // outro salão
        Expense::factory()->for($salon)->create(['description' => 'Aluguel', 'amount' => 150, 'due_date' => '2026-10-31']);
        Expense::factory()->for($salon)->create(['amount' => 50, 'due_date' => '2026-10-01', 'paid_at' => '2026-10-01']);

        $this->get(route('finance.index'))
            ->assertOk()
            ->assertSee('Outubro de 2026')
            ->assertSee('R$ 400,00')            // recebido
            ->assertSee('2 atendimentos · média R$ 200,00')
            ->assertSee('R$ 200,00')            // despesas
            ->assertSee('R$ 150,00 ainda a vencer')
            ->assertSee('R$ 160,00')            // comissões
            ->assertSee('R$ 40,00')             // lucro: 400 − 200 − 160
            ->assertSee('Cartão de crédito')
            ->assertSee('Aluguel')
            ->assertDontSee('R$ 999,00')
            ->assertDontSee('R$ 777,00');

        $this->get(route('finance.index', ['mes' => '2026-09']))->assertSee('Setembro de 2026')->assertSee('R$ 999,00');
        $this->get(route('finance.index', ['mes' => 'abc']))->assertOk()->assertSee('Outubro de 2026');
    }

    public function test_entries_list_payments_and_expenses_with_filter_and_search(): void
    {
        $salon = $this->user->salon;
        Payment::factory()->for($salon)->create(['description' => 'Juliana Alves — Coloração', 'amount' => 220]);
        Expense::factory()->for($salon)->create(['description' => 'Energia elétrica', 'amount' => 420, 'due_date' => '2026-10-20', 'paid_at' => '2026-10-20']);
        Expense::factory()->for($salon)->create(['description' => 'Contador', 'category' => 'Serviços', 'amount' => 450, 'due_date' => '2026-10-30']);

        $this->get(route('finance.entries'))
            ->assertOk()
            ->assertSee('+ R$ 220,00')
            ->assertSee('− R$ 870,00')
            ->assertSee('− R$ 650,00')          // saldo
            ->assertSeeInOrder(['Contador', 'Juliana Alves', 'Energia elétrica'])
            ->assertSee('A vencer');

        $this->get(route('finance.entries', ['tipo' => 'entradas']))->assertSee('Juliana Alves')->assertDontSee('Contador');
        $this->get(route('finance.entries', ['tipo' => 'saidas']))->assertSee('Contador')->assertDontSee('Juliana Alves');
        $this->get(route('finance.entries', ['busca' => 'energia']))->assertSee('Energia elétrica')->assertDontSee('Contador');
    }

    public function test_expense_can_be_created_paid_and_removed(): void
    {
        $this->post(route('finance.expenses.store'), [
            'description' => 'Bella Cosméticos', 'category' => 'Produtos', 'amount' => '1.640,50',
            'due_date' => '2026-11-05', 'method' => 'pix',
        ])->assertRedirect(route('finance.entries', ['mes' => '2026-11']));

        $expense = Expense::sole();
        $this->assertEquals(1640.5, $expense->amount);
        $this->assertFalse($expense->isPaid());

        $this->patch(route('finance.expenses.pay', $expense));
        $this->assertTrue($expense->fresh()->isPaid());

        $this->delete(route('finance.expenses.destroy', $expense));
        $this->assertModelMissing($expense);

        $this->post(route('finance.expenses.store'), ['description' => 'X', 'category' => 'Inventada', 'amount' => 'abc', 'due_date' => '2026-11-05'])
            ->assertSessionHasErrors(['category', 'amount']);
    }

    public function test_commissions_discount_advances_and_can_be_marked_paid(): void
    {
        $salon = $this->user->salon;
        $ana = Professional::factory()->for($salon)->create(['name' => 'Ana Souza', 'commission_percent' => 40]);
        Payment::factory()->for($salon)->create(['professional_id' => $ana->id, 'amount' => 500, 'commission' => 200]);
        Payment::factory()->for($salon)->create(['professional_id' => $ana->id, 'amount' => 250, 'commission' => 100]);

        $this->post(route('finance.advances.store'), [
            'professional_id' => $ana->id, 'amount' => '50', 'method' => 'pix', 'given_on' => '2026-10-08',
        ])->assertRedirect(route('finance.commissions', ['mes' => '2026-10']));

        $this->get(route('finance.commissions'))
            ->assertOk()
            ->assertSee('R$ 750,00')       // faturado
            ->assertSee('R$ 300,00')       // comissão
            ->assertSee('− R$ 50,00')      // vale
            ->assertSee('R$ 250,00')       // a pagar
            ->assertSee('Marcar como pago');

        $this->post(route('finance.commissions.pay', $ana), ['mes' => '2026-10'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('commission_payouts', ['professional_id' => $ana->id, 'amount' => 250]);

        $this->get(route('finance.commissions'))->assertDontSee('Marcar como pago')->assertSee('Desfazer');
        $this->post(route('finance.commissions.pay', $ana), ['mes' => '2026-10'])->assertSessionHasErrors('payout');

        $this->delete(route('finance.commissions.unpay', $ana), ['mes' => '2026-10']);
        $this->get(route('finance.commissions'))->assertSee('Marcar como pago');
    }

    public function test_another_salons_finance_records_are_out_of_reach(): void
    {
        $other = User::factory()->create();
        $expense = Expense::factory()->for($other->salon)->create();
        $advance = CommissionAdvance::create([
            'salon_id' => $other->salon_id,
            'professional_id' => Professional::factory()->for($other->salon)->create()->id,
            'amount' => 10, 'method' => PaymentMethod::Pix, 'given_on' => '2026-10-01',
        ]);
        $appointment = Appointment::factory()->for($other->salon)->create();

        $this->patch(route('finance.expenses.pay', $expense))->assertNotFound();
        $this->delete(route('finance.advances.destroy', $advance))->assertNotFound();
        $this->get(route('appointments.payment.create', $appointment))->assertNotFound();
        $this->post(route('finance.advances.store'), [
            'professional_id' => $advance->professional_id, 'amount' => '10', 'method' => 'pix', 'given_on' => '2026-10-01',
        ])->assertSessionHasErrors('professional_id', null, 'advance');

        $this->assertNull($expense->fresh()->paid_at);
    }

    public function test_company_pix_key_is_saved_and_shown_on_payment(): void
    {
        $this->patch(route('profile.salon'), ['salon_name' => 'Studio Bela Vista', 'pix_key' => '11.222.333/0001-44'])
            ->assertRedirect(route('profile.edit'));

        $this->get(route('profile.edit'))->assertSee('Dados da empresa salvos.');
        $this->assertSame('11.222.333/0001-44', $this->user->salon->fresh()->pix_key);

        $this->get(route('appointments.payment.create', $this->appointment()))->assertSee('11.222.333/0001-44');

        // O código interno do Breeze não aparece como aviso
        $this->patch(route('profile.update'), ['name' => 'João', 'email' => $this->user->email]);
        $this->get(route('profile.edit'))->assertDontSee('profile-updated');
    }

    public function test_money_parses_brazilian_amounts(): void
    {
        $this->assertSame(1234.56, Money::parse('1.234,56'));
        $this->assertSame(1500.0, Money::parse('1.500'));
        $this->assertSame(45.9, Money::parse('R$ 45,90'));
        $this->assertNull(Money::parse('abc'));
        $this->assertSame('R$ 1.234,50', Money::format(1234.5));
    }
}
