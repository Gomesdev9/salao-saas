<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\CommissionAdvance;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Vales: adiantamentos de comissão, descontados no fechamento do mês.
 */
class CommissionAdvanceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['amount' => Money::parse($request->input('amount'))]);

        $data = $request->validateWithBag('advance', [
            'professional_id' => ['required', Rule::exists('professionals', 'id')->where('salon_id', $request->user()->salon_id)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'given_on' => ['required', 'date_format:Y-m-d'],
        ]);

        $advance = CommissionAdvance::create($data);

        return redirect()
            ->route('finance.commissions', ['mes' => $advance->given_on->format('Y-m')])
            ->with('status', 'Vale de '.Money::format($advance->amount)." lançado para {$advance->professional->name}.");
    }

    public function destroy(CommissionAdvance $advance): RedirectResponse
    {
        $advance->delete();

        return back()->with('status', 'Vale removido.');
    }
}
