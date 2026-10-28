@use('App\Support\Money')

@php
    $mes = $month->format('Y-m');
    $totals = [
        'count' => $rows->sum('count'),
        'billed' => $rows->sum('billed'),
        'commission' => $rows->sum('commission'),
        'advance' => $rows->sum('advance'),
        'due' => $rows->sum('due'),
    ];
    $remaining = $rows->sum(fn ($row) => max($row['remaining'], 0));
    $percent = fn ($value) => rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',').'%';
    $defaultDate = $month->isSameMonth(today()) ? today() : $month;
@endphp

<x-page title="Comissões" eyebrow="Financeiro" subtitle="Calculadas sobre os atendimentos pagos, com o % de cada profissional.">
    <x-slot name="actions">
        <x-month-nav :month="$month" route="finance.commissions" />
        <button type="button" x-data @click="$dispatch('abrir-vale')"
                class="inline-flex h-11 items-center justify-center rounded-campo border border-linha bg-cartao px-4 text-sm font-semibold hover:bg-areia">Lançar vale</button>
    </x-slot>

    <x-finance-tabs :month="$month" />

    <div class="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-3">
        <x-stat label="Comissões do mês" :value="Money::format($totals['commission'])" />
        <x-stat label="Vales já adiantados" :value="Money::format($totals['advance'])" />
        <x-stat label="Falta pagar" :value="Money::format($remaining)" dark />
    </div>

    {{-- Formulário de vale: abre pelo botão "Lançar vale" --}}
    <section x-data="{ open: @js($errors->advance->any()) }" x-show="open" x-cloak
             @abrir-vale.window="open = true; $nextTick(() => $refs.valor.focus())" class="cartao p-5">
        <form method="POST" action="{{ route('finance.advances.store') }}" class="flex flex-col gap-4">
            @csrf
            <h2 class="text-xl font-semibold">Lançar vale</h2>
            <div class="grid grid-cols-[repeat(auto-fit,minmax(160px,1fr))] gap-3">
                <div>
                    <x-input-label for="professional_id" value="Profissional" />
                    <x-select-input id="professional_id" name="professional_id" required>
                        @foreach ($professionals as $professional)
                            <option value="{{ $professional->id }}" @selected((int) old('professional_id') === $professional->id)>{{ $professional->name }}</option>
                        @endforeach
                    </x-select-input>
                    <x-input-error :messages="$errors->advance->get('professional_id')" class="mt-1.5" />
                </div>
                <div>
                    <x-input-label for="advance_amount" value="Valor (R$)" />
                    <x-text-input id="advance_amount" name="amount" inputmode="decimal" placeholder="0,00" :value="old('amount')" x-ref="valor" required />
                    <x-input-error :messages="$errors->advance->get('amount')" class="mt-1.5" />
                </div>
                <div>
                    <x-input-label for="advance_method" value="Forma" />
                    <x-select-input id="advance_method" name="method" required>
                        @foreach ($methods as $method)
                            <option value="{{ $method->value }}" @selected(old('method') === $method->value)>{{ $method->label() }}</option>
                        @endforeach
                    </x-select-input>
                    <x-input-error :messages="$errors->advance->get('method')" class="mt-1.5" />
                </div>
                <div>
                    <x-input-label for="given_on" value="Data" />
                    <x-text-input id="given_on" name="given_on" type="date" :value="old('given_on', $defaultDate->toDateString())" required />
                    <x-input-error :messages="$errors->advance->get('given_on')" class="mt-1.5" />
                </div>
            </div>
            <div class="flex gap-2">
                <x-primary-button>Salvar vale</x-primary-button>
                <button type="button" @click="open = false" class="h-11 rounded-campo px-4 text-sm font-semibold hover:bg-areia">Cancelar</button>
            </div>
        </form>
    </section>

    <x-input-error :messages="$errors->get('payout')" />

    <section aria-label="Comissões por profissional" class="cartao overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] border-collapse text-sm [&_td]:whitespace-nowrap">
                <thead>
                    <tr class="bg-areia text-left text-xs uppercase tracking-wider text-suave">
                        <th class="px-4 py-3 font-semibold">Profissional</th>
                        <th class="px-2 py-3 text-right font-semibold">Atendimentos</th>
                        <th class="px-2 py-3 text-right font-semibold">Faturado</th>
                        <th class="px-2 py-3 text-right font-semibold">%</th>
                        <th class="px-2 py-3 text-right font-semibold">Comissão</th>
                        <th class="px-2 py-3 text-right font-semibold">Vales</th>
                        <th class="px-2 py-3 text-right font-semibold">A pagar</th>
                        <th class="px-4 py-3"><span class="sr-only">Ações</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $professional = $row['professional'];
                        @endphp
                        <tr class="border-t border-linha-clara">
                            <td class="px-4 py-3">
                                <span class="flex items-center gap-2.5">
                                    <x-avatar :name="$professional->name" size="h-9 w-9 text-xs" />
                                    <span class="font-semibold">{{ $professional->name }}</span>
                                </span>
                            </td>
                            <td class="px-2 py-3 text-right">{{ $row['count'] }}</td>
                            <td class="px-2 py-3 text-right">{{ Money::format($row['billed']) }}</td>
                            <td class="px-2 py-3 text-right">{{ $percent($professional->commission_percent) }}</td>
                            <td class="px-2 py-3 text-right">{{ Money::format($row['commission']) }}</td>
                            <td class="px-2 py-3 text-right text-suave">{{ $row['advance'] > 0 ? '− '.Money::format($row['advance']) : '—' }}</td>
                            <td class="px-2 py-3 text-right font-bold">{{ $row['due'] < 0 ? '− ' : '' }}{{ Money::format(abs($row['due'])) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">
                                @if ($row['remaining'] > 0)
                                    <form method="POST" action="{{ route('finance.commissions.pay', $professional) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="mes" value="{{ $mes }}">
                                        <button type="submit" class="min-h-10 rounded-campo bg-cafe px-3.5 text-[13px] font-semibold text-white hover:bg-cafe-escuro">
                                            {{ $row['paid'] > 0 ? 'Pagar o restante ('.Money::format($row['remaining']).')' : 'Marcar como pago' }}
                                        </button>
                                    </form>
                                @elseif ($row['paid'] > 0)
                                    <span class="inline-flex items-center gap-2">
                                        <span class="selo border-transparent bg-[#E7F4EC] text-[#1C5234]"><x-icon name="check" class="h-3.5 w-3.5" />Pago</span>
                                        <form method="POST" action="{{ route('finance.commissions.unpay', $professional) }}" onsubmit="return confirm(@js('Desfazer o pagamento da comissão de '.$professional->name.'?'))">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="mes" value="{{ $mes }}">
                                            <button type="submit" class="min-h-10 px-1 text-[13px] font-semibold text-suave hover:text-cafe">Desfazer</button>
                                        </form>
                                    </span>
                                @else
                                    <span class="text-suave">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-suave">Nenhum profissional ativo.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($rows->isNotEmpty())
                    <tfoot>
                        <tr class="border-t-2 border-linha font-bold">
                            <td class="px-4 py-3">Total</td>
                            <td class="px-2 py-3 text-right">{{ $totals['count'] }}</td>
                            <td class="px-2 py-3 text-right">{{ Money::format($totals['billed']) }}</td>
                            <td></td>
                            <td class="px-2 py-3 text-right">{{ Money::format($totals['commission']) }}</td>
                            <td class="px-2 py-3 text-right">{{ Money::format($totals['advance']) }}</td>
                            <td class="px-2 py-3 text-right">{{ Money::format($totals['due']) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>

    <section class="cartao flex max-w-[560px] flex-col gap-1 p-5">
        <h2 class="mb-2 text-xl font-semibold">Vales do mês</h2>
        @forelse ($advances as $advance)
            <div class="flex min-h-12 items-center gap-3 border-b border-linha-clara text-sm last:border-b-0">
                <span class="w-12 text-suave">{{ $advance->given_on->format('d/m') }}</span>
                <span class="min-w-0 flex-1 truncate font-semibold">{{ $advance->professional->name }}</span>
                <span class="text-suave">{{ $advance->method->label() }}</span>
                <span class="w-[100px] text-right font-semibold">{{ Money::format($advance->amount) }}</span>
                <x-remove-form :id="'remover-vale-'.$advance->id" :action="route('finance.advances.destroy', $advance)" confirm="Remover este vale?" />
                <button type="submit" form="remover-vale-{{ $advance->id }}" class="flex h-10 w-10 items-center justify-center rounded-full text-suave hover:bg-areia hover:text-perigo">
                    <span class="sr-only">Remover vale de {{ $advance->professional->name }}</span>
                    <x-icon name="fechar" class="h-4 w-4" />
                </button>
            </div>
        @empty
            <p class="text-sm text-suave">Nenhum vale lançado neste mês.</p>
        @endforelse
    </section>
</x-page>
