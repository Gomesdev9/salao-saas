<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Concerns\ResolvesMonth;
use App\Models\CommissionAdvance;
use App\Models\CommissionPayout;
use App\Models\Payment;
use App\Models\Professional;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Comissões do mês: o que cada profissional tem a receber, descontando os vales.
 */
class CommissionController extends Controller
{
    use ResolvesMonth;

    public function index(Request $request): View
    {
        $month = $this->month($request);
        $rows = $this->rows($month);

        return view('finance.commissions', [
            'month' => $month,
            'rows' => $rows,
            'advances' => CommissionAdvance::with('professional')
                ->whereBetween('given_on', [$month, $month->copy()->endOfMonth()])
                ->orderBy('given_on')
                ->get(),
            'professionals' => Professional::where('active', true)->orderBy('name')->get(),
            'methods' => [PaymentMethod::Pix, PaymentMethod::Cash],
        ]);
    }

    /**
     * Fecha a comissão do mês: registra o pagamento do que falta ao profissional.
     */
    public function pay(Request $request, Professional $professional): RedirectResponse
    {
        $month = $this->month($request);
        $row = $this->rows($month)->firstWhere('professional.id', $professional->id);

        if (! $row || $row['remaining'] <= 0) {
            return back()->withErrors(['payout' => "Não há comissão a pagar para {$professional->name} neste mês."]);
        }

        CommissionPayout::create([
            'professional_id' => $professional->id,
            'month' => $month,
            'amount' => $row['remaining'],
            'paid_at' => now(),
        ]);

        return back()->with('status', "Comissão de {$professional->name} paga: ".Money::format($row['remaining']).'.');
    }

    /**
     * Desfaz o "Marcar como pago" do mês.
     */
    public function unpay(Request $request, Professional $professional): RedirectResponse
    {
        CommissionPayout::where('professional_id', $professional->id)
            ->whereDate('month', $this->month($request)->toDateString())
            ->delete();

        return back()->with('status', "Pagamento da comissão de {$professional->name} desfeito.");
    }

    /**
     * Uma linha por profissional: atendimentos pagos, faturado, comissão, vales e quanto falta pagar.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function rows(Carbon $month): Collection
    {
        $end = $month->copy()->endOfMonth();

        $payments = Payment::whereNotNull('professional_id')->whereBetween('paid_at', [$month, $end])->get()->groupBy('professional_id');
        $advances = CommissionAdvance::whereBetween('given_on', [$month, $end])->get()->groupBy('professional_id');
        $payouts = CommissionPayout::whereDate('month', $month->toDateString())->get()->groupBy('professional_id');

        $ids = $payments->keys()->merge($advances->keys())->merge($payouts->keys());

        return Professional::where('active', true)
            ->orWhereIn('id', $ids)
            ->orderBy('name')
            ->get()
            ->map(function (Professional $professional) use ($payments, $advances, $payouts) {
                $paid = $payments->get($professional->id, collect());
                $commission = (float) $paid->sum('commission');
                $advance = (float) $advances->get($professional->id, collect())->sum('amount');
                $paidOut = (float) $payouts->get($professional->id, collect())->sum('amount');
                $due = round($commission - $advance, 2);

                return [
                    'professional' => $professional,
                    'count' => $paid->count(),
                    'billed' => (float) $paid->sum('amount'),
                    'commission' => $commission,
                    'advance' => $advance,
                    'due' => $due,
                    'paid' => $paidOut,
                    'remaining' => round($due - $paidOut, 2),
                ];
            });
    }
}
