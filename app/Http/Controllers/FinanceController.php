<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesMonth;
use App\Models\CommissionAdvance;
use App\Models\Expense;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FinanceController extends Controller
{
    use ResolvesMonth;

    private const ENTRIES_PER_PAGE = 30;

    /**
     * Visão do mês: recebido, despesas, comissões, lucro e gráficos.
     */
    public function index(Request $request): View
    {
        $month = $this->month($request);
        $end = $month->copy()->endOfMonth();

        $payments = Payment::with('professional')->whereBetween('paid_at', [$month, $end])->get();
        $expenses = Expense::whereBetween('due_date', [$month, $end])->get();

        $received = (float) $payments->sum('amount');
        $expensesTotal = (float) $expenses->sum('amount');
        $commissions = (float) $payments->sum('commission');

        $byMethod = $payments->groupBy(fn (Payment $p) => $p->method->value)
            ->map(fn (Collection $group) => ['method' => $group->first()->method, 'total' => (float) $group->sum('amount')])
            ->sortByDesc('total')
            ->values();

        $byProfessional = $payments->groupBy('professional_id')
            ->map(fn (Collection $group) => [
                'name' => $group->first()->professional?->name ?? 'Sem profissional',
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        return view('finance.index', [
            'month' => $month,
            'isCurrentMonth' => $month->isSameMonth(today()),
            'received' => $received,
            'paymentsCount' => $payments->count(),
            'expensesTotal' => $expensesTotal,
            'expensesOpen' => (float) $expenses->whereNull('paid_at')->sum('amount'),
            'commissions' => $commissions,
            'advances' => (float) CommissionAdvance::whereBetween('given_on', [$month, $end])->sum('amount'),
            'profit' => $received - $expensesTotal - $commissions,
            'chart' => $this->dailyChart($month, $payments),
            'byMethod' => $byMethod,
            'byProfessional' => $byProfessional,
            'bills' => Expense::whereNull('paid_at')->orderBy('due_date')->limit(4)->get(),
        ]);
    }

    /**
     * Lançamentos do mês: entradas (atendimentos pagos) e saídas (despesas), com filtro e busca.
     */
    public function entries(Request $request): View
    {
        $month = $this->month($request);
        $end = $month->copy()->endOfMonth();
        $type = in_array($request->query('tipo'), ['entradas', 'saidas']) ? $request->query('tipo') : null;
        $search = trim((string) $request->query('busca'));

        $payments = Payment::with('professional')->whereBetween('paid_at', [$month, $end]);
        $expenses = Expense::whereBetween('due_date', [$month, $end]);

        $incoming = (float) (clone $payments)->sum('amount');
        $outgoing = (float) (clone $expenses)->sum('amount');

        if ($search !== '') {
            $payments->where('description', 'like', "%{$search}%");
            $expenses->where(fn ($query) => $query->where('description', 'like', "%{$search}%")->orWhere('category', 'like', "%{$search}%"));
        }

        $rows = collect()
            ->concat($type === 'saidas' ? [] : $payments->get()->map(fn (Payment $payment) => [
                'date' => $payment->paid_at,
                'description' => $payment->description,
                'detail' => 'Atendimento'.($payment->professional ? " · {$payment->professional->name}" : ''),
                'method' => $payment->method->label().($payment->installments > 1 ? " {$payment->installments}x" : ''),
                'amount' => (float) $payment->amount,
                'status' => ['Recebido', 'bg-[#E7F4EC] text-[#1C5234]'],
                'appointment_id' => $payment->appointment_id,
                'expense' => null,
            ]))
            ->concat($type === 'entradas' ? [] : $expenses->get()->map(fn (Expense $expense) => [
                'date' => $expense->due_date,
                'description' => $expense->description,
                'detail' => $expense->category,
                'method' => $expense->method?->label() ?? '—',
                'amount' => -(float) $expense->amount,
                'status' => match (true) {
                    $expense->isPaid() => ['Pago', 'bg-[#F2EDE7] text-suave'],
                    $expense->isOverdue() => ['Vencida', 'bg-[#FBE9E7] text-[#8A2A1B]'],
                    default => ['A vencer', 'bg-mel text-mel-texto'],
                },
                'appointment_id' => null,
                'expense' => $expense,
            ]))
            ->sortByDesc(fn (array $row) => $row['date']->toDateString())
            ->values();

        // Entradas e saídas vêm de tabelas diferentes, então a paginação é feita na lista já juntada
        $page = LengthAwarePaginator::resolveCurrentPage();
        $rows = new LengthAwarePaginator($rows->forPage($page, self::ENTRIES_PER_PAGE)->values(), $rows->count(), self::ENTRIES_PER_PAGE, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('finance.entries', [
            'month' => $month,
            'type' => $type,
            'search' => $search,
            'rows' => $rows,
            'incoming' => $incoming,
            'outgoing' => $outgoing,
        ]);
    }

    /**
     * Uma barra por dia do mês (até hoje, no mês atual), com a escala do eixo já calculada.
     */
    private function dailyChart(Carbon $month, Collection $payments): array
    {
        $lastDay = match (true) {
            $month->isSameMonth(today()) => today()->day,
            $month->isFuture() => 0,
            default => $month->daysInMonth,
        };

        $totals = $payments->groupBy(fn (Payment $p) => $p->paid_at->day)->map(fn (Collection $g) => (float) $g->sum('amount'));

        // Escala "redonda" para o eixo: 4 faixas de 1, 2, 2,5 ou 5 vezes uma potência de 10
        $raw = max((float) $totals->max(), 100) / 4;
        $magnitude = 10 ** floor(log10($raw));
        $step = collect([1, 2, 2.5, 5, 10])->map(fn ($m) => $m * $magnitude)->first(fn ($s) => $s >= $raw);
        $top = $step * 4;

        $days = collect($lastDay > 0 ? range(1, $lastDay) : [])->map(function (int $day) use ($month, $totals, $top) {
            $date = $month->copy()->day($day);
            $value = $totals->get($day, 0.0);

            return [
                'day' => $day,
                'value' => $value,
                'percent' => round($value / $top * 100, 2),
                'title' => Str::ucfirst($date->translatedFormat('D')).', '.$date->format('d/m').($date->isToday() ? ' · hoje, parcial' : ''),
                'money' => $value > 0 ? Money::format($value) : 'Sem recebimentos',
            ];
        })->values();

        $best = $days->sortByDesc('value')->keys()->first();

        return [
            'days' => $days,
            'best' => $best,
            'ticks' => collect(range(0, 4))->map(fn (int $i) => [
                'percent' => $i * 25,
                'label' => $this->shortMoney($i * $step),
            ]),
        ];
    }

    /**
     * Valores curtos para o eixo do gráfico: "R$ 500", "R$ 1,5 mil".
     */
    private function shortMoney(float $value): string
    {
        if ($value >= 1000) {
            return 'R$ '.rtrim(rtrim(number_format($value / 1000, 1, ',', '.'), '0'), ',').' mil';
        }

        return 'R$ '.number_format($value, 0, ',', '.');
    }
}
