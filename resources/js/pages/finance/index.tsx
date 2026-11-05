import { Link } from '@inertiajs/react';
import { useState } from 'react';
import Avatar from '@/components/Avatar';
import { LinkButton } from '@/components/Buttons';
import FinanceTabs from '@/components/FinanceTabs';
import MonthNav, { type Month } from '@/components/MonthNav';
import Stat from '@/components/Stat';
import Page from '@/layouts/Page';
import { cx, money, plural } from '@/lib/format';

type Day = { day: number; value: number; percent: number; title: string; money: string };

type Props = {
    month: Month;
    title: string;
    today: string;
    isCurrentMonth: boolean;
    received: number;
    paymentsCount: number;
    expensesTotal: number;
    expensesOpen: number;
    commissions: number;
    advances: number;
    profit: number;
    /** Uma barra por dia do mês, com a escala do eixo já calculada */
    chart: { days: Day[]; best: number | null; ticks: { percent: number; label: string }[] };
    byMethod: { label: string; total: number }[];
    byProfessional: { name: string; count: number; total: number }[];
    bills: { id: number; description: string; day: string; monthName: string; month: string; amount: number; overdue: boolean }[];
};

/** Visão do mês: recebido, despesas, comissões, lucro e gráficos */
export default function FinanceIndex(props: Props) {
    const { month, received, paymentsCount, byMethod, byProfessional } = props;
    const maxMethod = Math.max(...byMethod.map((item) => item.total), 0) || 1;
    const maxProfessional = Math.max(...byProfessional.map((item) => item.total), 0) || 1;

    return (
        <Page
            title={props.title}
            eyebrow="Financeiro"
            subtitle={`${props.isCurrentMonth ? `Até hoje, ${props.today}. ` : ''}Os valores entram quando o atendimento é pago.`}
            actions={
                <>
                    <MonthNav month={month} routeName="finance.index" />
                    <LinkButton href={`${route('finance.entries', { mes: month.value })}#nova-despesa`} icon="mais">
                        Nova despesa
                    </LinkButton>
                </>
            }
        >
            <FinanceTabs month={month} />

            <div className="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-3">
                <Stat
                    label="Recebido"
                    value={money(received)}
                    hint={plural(paymentsCount, 'atendimento', 'atendimentos') + (paymentsCount ? ` · média ${money(received / paymentsCount)}` : '')}
                />
                <Stat
                    label="Despesas"
                    value={money(props.expensesTotal)}
                    href={route('finance.entries', { mes: month.value, tipo: 'saidas' })}
                    hint={props.expensesOpen > 0 ? `${money(props.expensesOpen)} ainda a vencer` : 'Todas pagas'}
                />
                <Stat
                    label="Comissões"
                    value={money(props.commissions)}
                    href={route('finance.commissions', { mes: month.value })}
                    hint={props.advances > 0 ? `${money(props.advances)} já adiantados em vales` : 'Nenhum vale no mês'}
                />
                <Stat label="Lucro estimado" value={money(props.profit)} hint="Recebido − despesas − comissões" dark />
            </div>

            <div className="flex flex-wrap items-stretch gap-5">
                <DailyChart {...props.chart} isCurrentMonth={props.isCurrentMonth} />

                <section className="cartao flex min-w-0 flex-[1_1_280px] flex-col gap-3.5 p-5">
                    <h2 className="text-xl font-semibold">Por forma de pagamento</h2>
                    {byMethod.length > 0 ? (
                        byMethod.map((item) => (
                            <div key={item.label} className="flex flex-col gap-1.5">
                                <div className="flex justify-between gap-2 text-sm">
                                    <span className="font-semibold">{item.label}</span>
                                    <span className="whitespace-nowrap">
                                        {money(item.total)} <span className="text-suave">· {Math.round((item.total / Math.max(received, 0.01)) * 100)}%</span>
                                    </span>
                                </div>
                                <Bar percent={(item.total / maxMethod) * 100} />
                            </div>
                        ))
                    ) : (
                        <p className="text-sm text-suave">Nenhum pagamento recebido neste mês.</p>
                    )}
                </section>
            </div>

            <div className="flex flex-wrap items-stretch gap-5">
                <section className="cartao flex min-w-0 flex-[2_1_460px] flex-col gap-3.5 p-5">
                    <div className="flex items-center justify-between">
                        <h2 className="text-xl font-semibold">Por profissional</h2>
                        <Link href={route('finance.commissions', { mes: month.value })} className="text-sm font-semibold text-cafe hover:text-cafe-escuro">
                            Ver comissões
                        </Link>
                    </div>
                    {byProfessional.length > 0 ? (
                        byProfessional.map((item) => (
                            <div key={item.name} className="flex items-center gap-3">
                                <Avatar name={item.name} size="h-9 w-9 text-xs" />
                                <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                                    <div className="flex justify-between gap-2 text-sm">
                                        <span className="min-w-0 truncate font-semibold">
                                            {item.name} <span className="font-normal text-suave">· {plural(item.count, 'atendimento', 'atendimentos')}</span>
                                        </span>
                                        <span className="whitespace-nowrap font-semibold">{money(item.total)}</span>
                                    </div>
                                    <Bar percent={(item.total / maxProfessional) * 100} />
                                </div>
                            </div>
                        ))
                    ) : (
                        <p className="text-sm text-suave">Os atendimentos pagos aparecem aqui, separados por profissional.</p>
                    )}
                </section>

                <section className="cartao flex min-w-0 flex-[1_1_280px] flex-col gap-1 p-5">
                    <h2 className="mb-2 text-xl font-semibold">Próximas contas</h2>
                    {props.bills.length > 0 ? (
                        props.bills.map((bill) => (
                            <Link
                                key={bill.id}
                                href={route('finance.entries', { mes: bill.month, tipo: 'saidas' })}
                                className="flex min-h-[52px] items-center gap-3 border-b border-linha-clara text-tinta last:border-b-0 hover:bg-areia/60"
                            >
                                <span className="flex w-11 shrink-0 flex-col items-center rounded-[10px] bg-areia py-1">
                                    <span className="text-[15px] font-bold">{bill.day}</span>
                                    <span className="text-[11px] text-suave">{bill.monthName}</span>
                                </span>
                                <span className="flex min-w-0 flex-1 flex-col">
                                    <span className="truncate text-sm font-semibold">{bill.description}</span>
                                    {bill.overdue && <span className="text-xs font-semibold text-perigo">Vencida</span>}
                                </span>
                                <span className="whitespace-nowrap text-sm font-semibold">{money(bill.amount)}</span>
                            </Link>
                        ))
                    ) : (
                        <p className="text-sm text-suave">Nenhuma conta a vencer.</p>
                    )}
                </section>
            </div>
        </Page>
    );
}

function Bar({ percent }: { percent: number }) {
    return (
        <div className="h-2 rounded bg-linha-clara">
            <div className="h-2 rounded bg-caramelo" style={{ width: `${Math.round(percent * 10) / 10}%` }} />
        </div>
    );
}

/** Gráfico de barras: recebido por dia */
function DailyChart({ days, best, ticks, isCurrentMonth }: Props['chart'] & { isCurrentMonth: boolean }) {
    const [selected, setSelected] = useState(best ?? 0);
    const day = days[selected];

    return (
        <section className="cartao flex min-w-0 flex-[2_1_460px] flex-col gap-4 p-5">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="flex flex-col gap-0.5">
                    <h2 className="text-xl font-semibold">Recebido por dia</h2>
                    <span className="text-[13px] text-suave">Passe o mouse ou use Tab nas barras</span>
                </div>
                {day && (
                    <div aria-live="polite" className="flex flex-col items-end gap-0.5">
                        <span className="text-[13px] text-suave">{day.title + (selected === best && day.money.startsWith('R$') ? ' · melhor dia' : '')}</span>
                        <span className="text-xl font-bold">{day.money}</span>
                    </div>
                )}
            </div>

            {days.length === 0 ? (
                <p className="py-10 text-center text-sm text-suave">Este mês ainda não começou.</p>
            ) : (
                <>
                    <div className="flex gap-2">
                        <div className="relative h-[220px] w-14 shrink-0" aria-hidden="true">
                            {ticks.map((tick) => (
                                <span key={tick.percent} className="absolute right-0 translate-y-1/2 text-[11px] text-suave" style={{ bottom: `${tick.percent}%` }}>
                                    {tick.label}
                                </span>
                            ))}
                        </div>
                        <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                            <div className="relative h-[220px]">
                                {ticks.map((tick) => (
                                    <div
                                        key={tick.percent}
                                        aria-hidden="true"
                                        className={cx('absolute inset-x-0 h-px', tick.percent === 0 ? 'bg-[#C9BCAF]' : 'bg-linha-clara')}
                                        style={{ bottom: `${tick.percent}%` }}
                                    />
                                ))}
                                <div className="absolute inset-0 flex">
                                    {days.map((item, i) => (
                                        <button
                                            key={item.day}
                                            type="button"
                                            aria-label={`${item.title}: ${item.money}`}
                                            onMouseEnter={() => setSelected(i)}
                                            onFocus={() => setSelected(i)}
                                            className="flex h-full min-w-0 flex-1 items-end justify-center focus:outline-none focus-visible:bg-areia"
                                        >
                                            {item.value > 0 ? (
                                                <span className={cx('block w-3/5 max-w-[18px] rounded-t', selected === i ? 'bg-cafe-escuro' : 'bg-caramelo')} style={{ height: `${item.percent}%` }} />
                                            ) : (
                                                <span className="block h-0.5 w-3/5 max-w-[18px] rounded-t bg-linha-forte" />
                                            )}
                                        </button>
                                    ))}
                                </div>
                            </div>
                            <div aria-hidden="true" className="flex">
                                {days.map((item) => (
                                    <span key={item.day} className={cx('min-w-0 flex-1 text-center text-[11px] text-suave', item.day % 2 === 0 && 'max-sm:invisible')}>
                                        {item.day}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>
                    <span className="text-xs text-suave">Dias sem recebimento aparecem em cinza.{isCurrentMonth ? ' Hoje ainda está em andamento.' : ''}</span>
                </>
            )}
        </section>
    );
}
