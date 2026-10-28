@use('App\Support\Money')

@php
    $client = $appointment->client;
    $professional = $appointment->professional;
    $service = $appointment->service;
    $percent = (float) $professional->commission_percent;
    $percentLabel = rtrim(rtrim(number_format($percent, 2, ',', '.'), '0'), ',').'%';
    $labels = collect($methods)->mapWithKeys(fn ($m) => [$m->value => $m->label()]);
    $state = [
        'subtotal' => (float) $appointment->price,
        'percent' => $percent,
        'method' => old('method', 'pix'),
        'discount' => (int) old('discount_percent', 0),
        'received' => old('cash_received', ''),
        'labels' => $labels,
    ];
    $pixKey = $appointment->salon->pix_key;
@endphp

<x-page title="Receber pagamento" :back="route('appointments.edit', $appointment)" back-label="Voltar para o agendamento">
    <form method="POST" action="{{ route('appointments.payment.store', $appointment) }}" x-data="pagamento(@js($state))"
          class="flex flex-wrap items-start gap-5">
        @csrf

        <div class="flex min-w-0 flex-[2_1_440px] flex-col gap-5">
            <section class="cartao flex flex-col gap-3 p-5">
                <h2 class="text-xl font-semibold">O que foi feito</h2>
                <div class="flex items-center gap-3 rounded-campo bg-areia px-3.5 py-3">
                    <span class="flex flex-1 flex-col gap-0.5">
                        <span class="text-[15px] font-semibold">{{ $service->name }}</span>
                        <span class="text-[13px] text-suave">{{ $service->duration_minutes }} min · com {{ $professional->name }}</span>
                    </span>
                    <span class="text-[15px] font-bold">{{ Money::format($appointment->price) }}</span>
                </div>
            </section>

            <fieldset class="cartao flex flex-col gap-3 p-5">
                <legend class="float-left mb-3 w-full font-display text-xl font-semibold">Desconto</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach ($discounts as $discount)
                        <label class="cursor-pointer">
                            <input type="radio" name="discount_percent" value="{{ $discount }}" x-model.number="discount" class="peer sr-only">
                            <span class="inline-flex min-h-11 items-center rounded-full border border-linha bg-cartao px-4 text-sm font-semibold hover:bg-areia peer-checked:border-tinta peer-checked:bg-tinta peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-caramelo">
                                {{ $discount ? "{$discount}%" : 'Sem desconto' }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('discount_percent')" />
            </fieldset>

            <fieldset class="cartao flex flex-col gap-3.5 p-5">
                <legend class="float-left mb-3.5 w-full font-display text-xl font-semibold">Forma de pagamento</legend>
                <div class="grid grid-cols-[repeat(auto-fit,minmax(120px,1fr))] gap-2.5">
                    @foreach ($methods as $method)
                        <label class="cursor-pointer">
                            <input type="radio" name="method" value="{{ $method->value }}" x-model="method" class="peer sr-only">
                            <span class="flex min-h-16 items-center justify-center rounded-2xl border-2 border-linha bg-cartao text-[15px] font-bold hover:bg-areia peer-checked:border-cafe peer-checked:bg-pele peer-checked:text-cafe-escuro peer-focus-visible:ring-2 peer-focus-visible:ring-caramelo">
                                {{ $method->label() }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('method')" />

                <div x-show="method === 'cash'" x-cloak class="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-3">
                    <div>
                        <x-input-label for="cash_received" value="Valor recebido" />
                        <x-text-input id="cash_received" name="cash_received" inputmode="decimal" placeholder="0,00" x-model="received" />
                        <x-input-error :messages="$errors->get('cash_received')" class="mt-1.5" />
                    </div>
                    <div>
                        <span class="mb-1.5 block text-sm font-semibold">Troco</span>
                        <span class="flex h-11 items-center rounded-campo px-3 text-[17px] font-bold"
                              :class="change === null || change >= 0 ? 'bg-[#E7F4EC] text-[#1C5234]' : 'bg-[#FBE9E7] text-[#8A2A1B]'"
                              x-text="change === null ? '—' : (change >= 0 ? brl(change) : 'Faltam ' + brl(-change))"></span>
                    </div>
                </div>

                <div x-show="method === 'credit'" x-cloak class="max-w-[260px]">
                    <x-input-label for="installments" value="Parcelas" />
                    <x-select-input id="installments" name="installments">
                        <option value="1">À vista</option>
                        <option value="2" @selected(old('installments') == 2)>2x sem juros</option>
                        <option value="3" @selected(old('installments') == 3)>3x sem juros</option>
                    </x-select-input>
                </div>

                <div x-show="method === 'pix'" x-cloak class="flex flex-wrap items-center gap-3 rounded-campo bg-areia px-3.5 py-3 text-sm">
                    @if ($pixKey)
                        <span class="min-w-0 flex-1 break-all">Chave Pix da empresa: <strong>{{ $pixKey }}</strong></span>
                        <button type="button" x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText(@js($pixKey)); copied = true; setTimeout(() => copied = false, 2000)"
                                class="min-h-10 rounded-campo border border-linha-forte bg-cartao px-3.5 text-[13px] font-semibold hover:bg-creme"
                                x-text="copied ? 'Copiada!' : 'Copiar chave'">Copiar chave</button>
                    @else
                        <span class="flex-1">A chave Pix da empresa ainda não foi cadastrada.</span>
                        <a href="{{ route('profile.edit') }}#empresa" class="font-semibold text-cafe hover:text-cafe-escuro">Cadastrar chave Pix</a>
                    @endif
                </div>
            </fieldset>
        </div>

        <aside aria-label="Resumo" class="cartao flex min-w-0 flex-[1_1_280px] flex-col gap-3.5 p-5">
            <div class="flex items-center gap-3">
                <x-avatar :name="$client->name" size="h-11 w-11 text-[15px]" />
                <div class="flex flex-col">
                    <span class="text-base font-bold">{{ $client->name }}</span>
                    <span class="text-[13px] text-suave">{{ $appointment->starts_at->format('d/m') }} · {{ $appointment->starts_at->format('H:i') }}–{{ $appointment->ends_at->format('H:i') }}</span>
                </div>
            </div>
            <dl class="flex flex-col gap-2 border-y border-linha-clara py-3.5 text-sm">
                <div class="flex justify-between"><dt class="text-suave">Subtotal</dt><dd>{{ Money::format($appointment->price) }}</dd></div>
                <div class="flex justify-between"><dt class="text-suave">Desconto</dt><dd x-text="discount ? '− ' + brl(subtotal - total) : brl(0)">{{ Money::format(0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-suave">Forma</dt><dd x-text="labels[method]">Pix</dd></div>
            </dl>
            <div class="flex items-baseline justify-between">
                <span class="text-[15px] font-semibold">Total</span>
                <span class="font-display text-[32px] font-bold" x-text="brl(total)">{{ Money::format($appointment->price) }}</span>
            </div>
            <span class="text-[13px] text-suave">Comissão de {{ $professional->name }} ({{ $percentLabel }}): <span x-text="brl(commission)"></span></span>
            <button type="submit" class="flex h-12 items-center justify-center gap-2 rounded-campo bg-cafe text-[15px] font-bold text-white hover:bg-cafe-escuro focus:outline-none focus:ring-2 focus:ring-caramelo focus:ring-offset-2">
                <x-icon name="check" class="h-[18px] w-[18px]" />Confirmar pagamento
            </button>
            <span class="text-[13px] leading-5 text-suave">O horário fica como “Atendido” e o valor entra nos lançamentos do dia.</span>
        </aside>
    </form>
</x-page>
