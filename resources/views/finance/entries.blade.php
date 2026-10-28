@use('App\Support\Money')
@use('App\Enums\PaymentMethod')
@use('App\Models\Expense')

@php
    $monthName = $month->translatedFormat('F');
    $params = ['mes' => $month->format('Y-m')];
    $filters = [null => 'Todos', 'entradas' => 'Entradas', 'saidas' => 'Saídas'];
    $balance = $incoming - $outgoing;
    $defaultDue = $month->isSameMonth(today()) ? today() : $month;
@endphp

<x-page title="Lançamentos" eyebrow="Financeiro" :subtitle="'Tudo o que entrou e saiu do caixa em '.$monthName.'.'">
    <x-slot name="actions">
        <x-month-nav :month="$month" route="finance.entries" />
    </x-slot>

    <x-finance-tabs :month="$month" />

    <div class="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-3">
        <div class="cartao flex flex-col gap-1 px-5 py-4">
            <span class="text-[13px] text-suave">Entradas</span>
            <span class="text-[22px] font-bold text-[#1C5234]">+ {{ Money::format($incoming) }}</span>
        </div>
        <div class="cartao flex flex-col gap-1 px-5 py-4">
            <span class="text-[13px] text-suave">Saídas</span>
            <span class="text-[22px] font-bold text-[#8A2A1B]">− {{ Money::format($outgoing) }}</span>
        </div>
        <div class="cartao flex flex-col gap-1 px-5 py-4">
            <span class="text-[13px] text-suave">Saldo do mês</span>
            <span class="text-[22px] font-bold">{{ $balance < 0 ? '− ' : '' }}{{ Money::format(abs($balance)) }}</span>
        </div>
    </div>

    <div class="flex flex-wrap items-start gap-5">
        <section aria-label="Lançamentos do mês" class="cartao min-w-0 flex-[2_1_520px] overflow-hidden">
            <div class="flex flex-wrap items-center gap-2.5 border-b border-linha px-4 py-3.5">
                @foreach ($filters as $value => $label)
                    @php
                        $on = $type === ($value ?: null);
                    @endphp
                    <a href="{{ route('finance.entries', [...$params, 'tipo' => $value ?: null, 'busca' => $search ?: null]) }}" @if ($on) aria-current="page" @endif
                       class="inline-flex min-h-10 items-center rounded-full border px-3.5 text-[13px] font-semibold {{ $on ? 'border-tinta bg-tinta text-white' : 'border-linha bg-cartao hover:bg-areia' }}">{{ $label }}</a>
                @endforeach
                <form method="GET" action="{{ route('finance.entries') }}" role="search" class="ml-auto min-w-[200px] flex-[0_1_260px]">
                    <input type="hidden" name="mes" value="{{ $month->format('Y-m') }}">
                    @if ($type)
                        <input type="hidden" name="tipo" value="{{ $type }}">
                    @endif
                    <label class="flex h-10 items-center gap-2 rounded-campo border border-linha-forte bg-cartao px-3 text-suave focus-within:border-caramelo">
                        <x-icon name="busca" class="h-4 w-4" />
                        <span class="sr-only">Buscar lançamento</span>
                        <input type="search" name="busca" value="{{ $search }}" placeholder="Buscar"
                               class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-tinta placeholder:text-suave/60 focus:ring-0">
                    </label>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] border-collapse text-sm">
                    <thead>
                        <tr class="bg-areia text-left text-xs uppercase tracking-wider text-suave">
                            <th class="px-4 py-2.5 font-semibold">Data</th>
                            <th class="min-w-[190px] px-3 py-2.5 font-semibold">Descrição</th>
                            <th class="px-3 py-2.5 font-semibold">Forma</th>
                            <th class="px-3 py-2.5 text-right font-semibold">Valor</th>
                            <th class="px-3 py-2.5 font-semibold">Situação</th>
                            <th class="px-2 py-2.5"><span class="sr-only">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="border-t border-linha-clara">
                                <td class="whitespace-nowrap px-4 py-3 text-suave">{{ $row['date']->format('d/m') }}</td>
                                <td class="px-3 py-3">
                                    <span class="flex flex-col gap-0.5">
                                        @if ($row['appointment_id'])
                                            <a href="{{ route('appointments.edit', $row['appointment_id']) }}" class="font-semibold text-tinta hover:text-cafe">{{ $row['description'] }}</a>
                                        @else
                                            <span class="font-semibold">{{ $row['description'] }}</span>
                                        @endif
                                        <span class="text-xs text-suave">{{ $row['detail'] }}</span>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3">{{ $row['method'] }}</td>
                                <td class="whitespace-nowrap px-3 py-3 text-right font-bold {{ $row['amount'] > 0 ? 'text-[#1C5234]' : 'text-[#8A2A1B]' }}">
                                    {{ $row['amount'] > 0 ? '+' : '−' }} {{ Money::format(abs($row['amount'])) }}
                                </td>
                                <td class="px-3 py-3"><span class="selo border-transparent {{ $row['status'][1] }}">{{ $row['status'][0] }}</span></td>
                                <td class="whitespace-nowrap px-2 py-1.5 text-right">
                                    @if ($expense = $row['expense'])
                                        <div class="flex items-center justify-end">
                                            @unless ($expense->isPaid())
                                                <form method="POST" action="{{ route('finance.expenses.pay', $expense) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" title="Marcar como paga" class="flex h-10 w-10 items-center justify-center rounded-full text-cafe hover:bg-areia">
                                                        <span class="sr-only">Marcar {{ $expense->description }} como paga</span>
                                                        <x-icon name="check" class="h-[18px] w-[18px]" />
                                                    </button>
                                                </form>
                                            @endunless
                                            <x-remove-form :id="'remover-despesa-'.$expense->id" :action="route('finance.expenses.destroy', $expense)" :confirm="'Remover a despesa '.$expense->description.'?'" />
                                            <button type="submit" form="remover-despesa-{{ $expense->id }}" title="Remover" class="flex h-10 w-10 items-center justify-center rounded-full text-suave hover:bg-areia hover:text-perigo">
                                                <span class="sr-only">Remover {{ $expense->description }}</span>
                                                <x-icon name="fechar" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-suave">
                                    {{ $search !== '' ? "Nada encontrado para “{$search}”." : "Nenhum lançamento em {$monthName}." }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-linha px-4 py-3 text-[13px] text-suave">
                <span>
                    @if ($rows->total() > $rows->count())
                        Mostrando {{ $rows->firstItem() }}–{{ $rows->lastItem() }} de {{ $rows->total() }} lançamentos
                    @else
                        {{ $rows->total() }} {{ $rows->total() === 1 ? 'lançamento' : 'lançamentos' }}
                    @endif
                    {{ $search !== '' ? "com “{$search}” " : '' }}em {{ $monthName }}
                </span>
                @if ($rows->hasPages())
                    <div class="flex gap-1.5 text-sm">
                        @if ($prev = $rows->previousPageUrl())
                            <a href="{{ $prev }}" class="inline-flex h-11 items-center rounded-campo border border-linha px-3.5 font-semibold text-tinta hover:bg-areia">Anterior</a>
                        @endif
                        <span class="inline-flex h-11 items-center rounded-campo bg-tinta px-4 font-semibold text-white" aria-current="page">{{ $rows->currentPage() }}</span>
                        @if ($next = $rows->nextPageUrl())
                            <a href="{{ $next }}" class="inline-flex h-11 items-center rounded-campo border border-linha px-3.5 font-semibold text-tinta hover:bg-areia">Próxima</a>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        <form id="nova-despesa" method="POST" action="{{ route('finance.expenses.store') }}" aria-labelledby="titulo-nova-despesa"
              class="cartao flex min-w-0 flex-[1_1_280px] scroll-mt-6 flex-col gap-3.5 p-5">
            @csrf
            <h2 id="titulo-nova-despesa" class="text-xl font-semibold">Nova despesa</h2>

            <div>
                <x-input-label for="description" value="Descrição" />
                <x-text-input id="description" name="description" :value="old('description')" placeholder="Ex.: Conta de luz" required maxlength="120" />
                <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="category" value="Categoria" />
                <x-select-input id="category" name="category" required>
                    @foreach (Expense::CATEGORIES as $category)
                        <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('category')" class="mt-1.5" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="min-w-0">
                    <x-input-label for="amount" value="Valor (R$)" />
                    <x-text-input id="amount" name="amount" inputmode="decimal" :value="old('amount')" placeholder="0,00" required />
                    <x-input-error :messages="$errors->get('amount')" class="mt-1.5" />
                </div>
                <div class="min-w-0">
                    <x-input-label for="due_date" value="Vencimento" />
                    <x-text-input id="due_date" name="due_date" type="date" :value="old('due_date', $defaultDue->toDateString())" required />
                    <x-input-error :messages="$errors->get('due_date')" class="mt-1.5" />
                </div>
            </div>

            <div>
                <x-input-label for="method" value="Forma de pagamento" />
                <x-select-input id="method" name="method">
                    <option value="">—</option>
                    @foreach (PaymentMethod::cases() as $method)
                        <option value="{{ $method->value }}" @selected(old('method') === $method->value)>{{ $method->label() }}</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('method')" class="mt-1.5" />
            </div>

            <x-checkbox-input name="paid" value="1" label="Já está pago" :checked="(bool) old('paid')" />

            <x-primary-button>Salvar despesa</x-primary-button>
            <span class="text-[13px] leading-5 text-suave">As entradas vêm sozinhas: cada atendimento pago na agenda vira um lançamento.</span>
        </form>
    </div>
</x-page>
