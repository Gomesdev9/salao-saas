@use('App\Support\Money')

@php
    $title = Str::ucfirst($month->translatedFormat('F \d\e Y'));
    $subtitle = ($isCurrentMonth ? 'Até hoje, '.today()->format('d/m').'. ' : '').'Os valores entram quando o atendimento é pago.';
    $days = $chart['days'];
    $bestDay = $chart['best'];
    $maxMethod = $byMethod->max('total') ?: 1;
    $maxProfessional = $byProfessional->max('total') ?: 1;
@endphp

<x-page :title="$title" eyebrow="Financeiro" :subtitle="$subtitle">
    <x-slot name="actions">
        <x-month-nav :month="$month" route="finance.index" />
        <x-link-button :href="route('finance.entries', ['mes' => $month->format('Y-m')]).'#nova-despesa'" icon="mais">Nova despesa</x-link-button>
    </x-slot>

    <x-finance-tabs :month="$month" />

    <div class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-3">
        <x-stat label="Recebido" :value="Money::format($received)"
                :hint="$paymentsCount.' '.($paymentsCount === 1 ? 'atendimento' : 'atendimentos').($paymentsCount ? ' · média '.Money::format($received / $paymentsCount) : '')" />
        <x-stat label="Despesas" :value="Money::format($expensesTotal)" :href="route('finance.entries', ['mes' => $month->format('Y-m'), 'tipo' => 'saidas'])"
                :hint="$expensesOpen > 0 ? Money::format($expensesOpen).' ainda a vencer' : 'Todas pagas'" />
        <x-stat label="Comissões" :value="Money::format($commissions)" :href="route('finance.commissions', ['mes' => $month->format('Y-m')])"
                :hint="$advances > 0 ? Money::format($advances).' já adiantados em vales' : 'Nenhum vale no mês'" />
        <x-stat label="Lucro estimado" :value="Money::format($profit)" hint="Recebido − despesas − comissões" dark />
    </div>

    <div class="flex flex-wrap items-stretch gap-5">
        {{-- Gráfico de barras: recebido por dia --}}
        <section class="cartao flex min-w-0 flex-[2_1_460px] flex-col gap-4 p-5"
                 x-data="{ sel: @js($bestDay), days: @js($days->map(fn ($d) => ['title' => $d['title'], 'money' => $d['money']])) }">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex flex-col gap-0.5">
                    <h2 class="text-xl font-semibold">Recebido por dia</h2>
                    <span class="text-[13px] text-suave">Passe o mouse ou use Tab nas barras</span>
                </div>
                @if ($days->isNotEmpty())
                    <div aria-live="polite" class="flex flex-col items-end gap-0.5">
                        <span class="text-[13px] text-suave" x-text="days[sel].title + (sel === {{ $bestDay }} && days[sel].money.startsWith('R$') ? ' · melhor dia' : '')">{{ $days[$bestDay]['title'] }}</span>
                        <span class="text-xl font-bold" x-text="days[sel].money">{{ $days[$bestDay]['money'] }}</span>
                    </div>
                @endif
            </div>

            @if ($days->isEmpty())
                <p class="py-10 text-center text-sm text-suave">Este mês ainda não começou.</p>
            @else
                <div class="flex gap-2">
                    <div class="relative h-[220px] w-14 shrink-0" aria-hidden="true">
                        @foreach ($chart['ticks'] as $tick)
                            <span class="absolute right-0 translate-y-1/2 text-[11px] text-suave" style="bottom: {{ $tick['percent'] }}%">{{ $tick['label'] }}</span>
                        @endforeach
                    </div>
                    <div class="flex min-w-0 flex-1 flex-col gap-1.5">
                        <div class="relative h-[220px]">
                            @foreach ($chart['ticks'] as $tick)
                                <div aria-hidden="true" class="absolute inset-x-0 h-px {{ $tick['percent'] === 0 ? 'bg-[#C9BCAF]' : 'bg-linha-clara' }}" style="bottom: {{ $tick['percent'] }}%"></div>
                            @endforeach
                            <div class="absolute inset-0 flex">
                                @foreach ($days as $i => $day)
                                    <button type="button" aria-label="{{ $day['title'] }}: {{ $day['money'] }}" @mouseenter="sel = {{ $i }}" @focus="sel = {{ $i }}"
                                            class="flex h-full min-w-0 flex-1 items-end justify-center focus:outline-none focus-visible:bg-areia">
                                        @if ($day['value'] > 0)
                                            <span class="block w-3/5 max-w-[18px] rounded-t" :class="sel === {{ $i }} ? 'bg-cafe-escuro' : 'bg-caramelo'" style="height: {{ $day['percent'] }}%"></span>
                                        @else
                                            <span class="block h-0.5 w-3/5 max-w-[18px] rounded-t bg-linha-forte"></span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div aria-hidden="true" class="flex">
                            @foreach ($days as $day)
                                <span class="min-w-0 flex-1 text-center text-[11px] text-suave {{ $day['day'] % 2 === 0 ? 'max-sm:invisible' : '' }}">{{ $day['day'] }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <span class="text-xs text-suave">Dias sem recebimento aparecem em cinza.{{ $isCurrentMonth ? ' Hoje ainda está em andamento.' : '' }}</span>
            @endif
        </section>

        <section class="cartao flex min-w-0 flex-[1_1_280px] flex-col gap-3.5 p-5">
            <h2 class="text-xl font-semibold">Por forma de pagamento</h2>
            @forelse ($byMethod as $item)
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between gap-2 text-sm">
                        <span class="font-semibold">{{ $item['method']->longLabel() }}</span>
                        <span class="whitespace-nowrap">{{ Money::format($item['total']) }} <span class="text-suave">· {{ round($item['total'] / max($received, 0.01) * 100) }}%</span></span>
                    </div>
                    <div class="h-2 rounded bg-linha-clara"><div class="h-2 rounded bg-caramelo" style="width: {{ round($item['total'] / $maxMethod * 100, 1) }}%"></div></div>
                </div>
            @empty
                <p class="text-sm text-suave">Nenhum pagamento recebido neste mês.</p>
            @endforelse
        </section>
    </div>

    <div class="flex flex-wrap items-stretch gap-5">
        <section class="cartao flex min-w-0 flex-[2_1_460px] flex-col gap-3.5 p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Por profissional</h2>
                <a href="{{ route('finance.commissions', ['mes' => $month->format('Y-m')]) }}" class="text-sm font-semibold text-cafe hover:text-cafe-escuro">Ver comissões</a>
            </div>
            @forelse ($byProfessional as $item)
                <div class="flex items-center gap-3">
                    <x-avatar :name="$item['name']" size="h-9 w-9 text-xs" />
                    <div class="flex min-w-0 flex-1 flex-col gap-1.5">
                        <div class="flex justify-between gap-2 text-sm">
                            <span class="min-w-0 truncate font-semibold">{{ $item['name'] }} <span class="font-normal text-suave">· {{ $item['count'] }} {{ $item['count'] === 1 ? 'atendimento' : 'atendimentos' }}</span></span>
                            <span class="whitespace-nowrap font-semibold">{{ Money::format($item['total']) }}</span>
                        </div>
                        <div class="h-2 rounded bg-linha-clara"><div class="h-2 rounded bg-caramelo" style="width: {{ round($item['total'] / $maxProfessional * 100, 1) }}%"></div></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-suave">Os atendimentos pagos aparecem aqui, separados por profissional.</p>
            @endforelse
        </section>

        <section class="cartao flex min-w-0 flex-[1_1_280px] flex-col gap-1 p-5">
            <h2 class="mb-2 text-xl font-semibold">Próximas contas</h2>
            @forelse ($bills as $bill)
                <a href="{{ route('finance.entries', ['mes' => $bill->due_date->format('Y-m'), 'tipo' => 'saidas']) }}" class="flex min-h-[52px] items-center gap-3 border-b border-linha-clara text-tinta last:border-b-0 hover:bg-areia/60">
                    <span class="flex w-11 shrink-0 flex-col items-center rounded-[10px] bg-areia py-1">
                        <span class="text-[15px] font-bold">{{ $bill->due_date->format('d') }}</span>
                        <span class="text-[11px] text-suave">{{ $bill->due_date->translatedFormat('M') }}</span>
                    </span>
                    <span class="flex min-w-0 flex-1 flex-col">
                        <span class="truncate text-sm font-semibold">{{ $bill->description }}</span>
                        @if ($bill->isOverdue())
                            <span class="text-xs font-semibold text-perigo">Vencida</span>
                        @endif
                    </span>
                    <span class="whitespace-nowrap text-sm font-semibold">{{ Money::format($bill->amount) }}</span>
                </a>
            @empty
                <p class="text-sm text-suave">Nenhuma conta a vencer.</p>
            @endforelse
        </section>
    </div>
</x-page>
