<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Expense;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Despesas (saídas do caixa). As entradas vêm sozinhas dos atendimentos pagos.
 */
class ExpenseController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['amount' => Money::parse($request->input('amount'))]);

        $data = $request->validate([
            'description' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(Expense::CATEGORIES)],
            'method' => ['nullable', Rule::enum(PaymentMethod::class)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'due_date' => ['required', 'date_format:Y-m-d'],
        ]);

        $expense = Expense::create([
            ...$data,
            'paid_at' => $request->boolean('paid') ? today() : null,
        ]);

        return redirect()
            ->route('finance.entries', ['mes' => $expense->due_date->format('Y-m')])
            ->with('status', 'Despesa salva: '.$expense->description.', '.Money::format($expense->amount).'.');
    }

    public function pay(Expense $expense): RedirectResponse
    {
        $expense->update(['paid_at' => today()]);

        return back()->with('status', "{$expense->description} marcada como paga.");
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('status', "Despesa {$expense->description} removida.");
    }
}
