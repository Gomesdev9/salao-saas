<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentMethod;
use App\Models\Appointment;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Receber o pagamento de um atendimento: vira uma entrada no financeiro e o horário fica como "Atendido".
 */
class PaymentController extends Controller
{
    /** Descontos oferecidos na tela, em %. */
    public const DISCOUNTS = [0, 5, 10];

    public function create(Appointment $appointment): Response|RedirectResponse
    {
        if ($redirect = $this->refuse($appointment)) {
            return $redirect;
        }

        $appointment->load(['client', 'professional', 'service', 'salon']);

        $professional = $appointment->professional;
        $percent = (float) $professional->commission_percent;

        return Inertia::render('payments/create', [
            'appointment' => [
                'id' => $appointment->id,
                'client' => $appointment->client->name,
                'when' => $appointment->starts_at->format('d/m').' · '.$appointment->starts_at->format('H:i').'–'.$appointment->ends_at->format('H:i'),
                'service' => $appointment->service->name,
                'duration' => $appointment->service->duration_minutes,
                'professional' => $professional->name,
                'price' => (float) $appointment->price,
            ],
            'commission' => [
                'percent' => $percent,
                // 40,00 vira "40%" e 12,50 vira "12,5%"
                'label' => rtrim(rtrim(number_format($percent, 2, ',', '.'), '0'), ',').'%',
            ],
            'methods' => array_map(fn (PaymentMethod $method) => ['value' => $method->value, 'label' => $method->label()], PaymentMethod::forAppointments()),
            'discounts' => self::DISCOUNTS,
            'pixKey' => $appointment->salon->pix_key,
        ]);
    }

    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        if ($redirect = $this->refuse($appointment)) {
            return $redirect;
        }

        $request->merge(['cash_received' => Money::parse($request->input('cash_received'))]);

        $data = $request->validate([
            'method' => ['required', Rule::in(array_map(fn ($m) => $m->value, PaymentMethod::forAppointments()))],
            'discount_percent' => ['required', 'integer', Rule::in(self::DISCOUNTS)],
            'installments' => ['nullable', 'integer', 'between:1,3'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
        ]);

        $method = PaymentMethod::from($data['method']);
        $subtotal = (float) $appointment->price;
        $amount = round($subtotal * (100 - $data['discount_percent']) / 100, 2);
        $cashReceived = $method === PaymentMethod::Cash ? $data['cash_received'] : null;

        if ($cashReceived !== null && $cashReceived < $amount) {
            throw ValidationException::withMessages([
                'cash_received' => 'O valor recebido é menor que o total de '.Money::format($amount).'.',
            ]);
        }

        $professional = $appointment->professional;
        $percent = (float) $professional->commission_percent;

        $appointment->payment()->create([
            'professional_id' => $professional->id,
            'description' => "{$appointment->client->name} — {$appointment->service->name}",
            'method' => $method,
            'subtotal' => $subtotal,
            'discount_percent' => $data['discount_percent'],
            'amount' => $amount,
            'installments' => $method === PaymentMethod::Credit ? ($data['installments'] ?? 1) : 1,
            'cash_received' => $cashReceived,
            'commission_percent' => $percent,
            'commission' => round($amount * $percent / 100, 2),
            'paid_at' => now(),
        ]);

        $appointment->update(['status' => AppointmentStatus::Done]);

        return redirect()
            ->route('appointments.index', ['data' => $appointment->starts_at->toDateString()])
            ->with('status', 'Pagamento de '.Money::format($amount).' recebido.');
    }

    /**
     * Desfaz um pagamento lançado por engano. O horário continua como estava.
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $appointment->payment()->delete();

        return redirect()
            ->route('appointments.edit', $appointment)
            ->with('status', 'Pagamento desfeito. O valor saiu dos lançamentos.');
    }

    /**
     * Horário já pago, cancelado ou em que o cliente faltou não pode receber pagamento.
     */
    private function refuse(Appointment $appointment): ?RedirectResponse
    {
        $reason = match (true) {
            $appointment->payment()->exists() => 'Este horário já foi pago.',
            ! in_array($appointment->status, AppointmentStatus::occupying()) => "Este horário está como {$appointment->status->label()}. Mude a situação antes de receber.",
            default => null,
        };

        return $reason
            ? redirect()->route('appointments.edit', $appointment)->withErrors(['payment' => $reason])
            : null;
    }
}
