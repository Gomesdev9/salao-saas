<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BusinessType;
use App\Enums\Plan;
use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/register', [
            'businessTypes' => array_map(fn (BusinessType $type) => ['value' => $type->value, 'label' => $type->label()], BusinessType::cases()),
            'plans' => Plan::allProps(),
            // Plano escolhido na página de divulgação (?plano=premium); sem escolha, fica o mais indicado
            'selectedPlan' => (Plan::tryFrom((string) $request->query('plano')) ?? Plan::Professional)->value,
            'trialDays' => Plan::TRIAL_DAYS,
        ]);
    }

    /**
     * Cadastra uma nova empresa (salão, clínica...) junto com o usuário proprietário dela.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'salon_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', Rule::enum(BusinessType::class)],
            'plan' => ['required', Rule::enum(Plan::class)],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [], ['phone' => 'WhatsApp da empresa']);

        // A transação garante que empresa e usuário são criados juntos (ou nenhum dos dois)
        $user = DB::transaction(function () use ($request) {
            $salon = Salon::create([
                'name' => $request->salon_name,
                'slug' => Salon::uniqueSlug($request->salon_name),
                'business_type' => $request->business_type,
                'plan' => $request->plan,
                'trial_ends_at' => now()->addDays(Plan::TRIAL_DAYS),
                'phone' => $request->phone,
                'city' => $request->city,
            ]);

            return User::create([
                'salon_id' => $salon->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'owner',
            ]);
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))
            ->with('status', 'Conta criada! Você tem '.Plan::TRIAL_DAYS.' dias grátis. Siga os primeiros passos abaixo para deixar o sistema pronto.');
    }
}
