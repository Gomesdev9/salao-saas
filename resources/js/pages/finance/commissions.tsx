import { router, useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import Avatar from '@/components/Avatar';
import { PrimaryButton } from '@/components/Buttons';
import FinanceTabs from '@/components/FinanceTabs';
import { InputError, InputLabel, SelectInput, TextInput } from '@/components/Form';
import Icon from '@/components/Icon';
import MonthNav, { type Month } from '@/components/MonthNav';
import Stat from '@/components/Stat';
import Page from '@/layouts/Page';
import { money } from '@/lib/format';
import type { Option } from '@/types';

type Row = {
    professional: { id: number; name: string; percent: string };
    count: number;
    billed: number;
    commission: number;
    advance: number;
    due: number;
    paid: number;
    remaining: number;
};

type Props = {
    month: Month;
    rows: Row[];
    advances: { id: number; date: string; professional: string; method: string; amount: number }[];
    professionals: { id: number; name: string }[];
    methods: Option[];
    defaultDate: string;
    errors: Record<string, string>;
};

const sum = (rows: Row[], key: 'count' | 'billed' | 'commission' | 'advance' | 'due') => rows.reduce((total, row) => total + row[key], 0);

/** Comissões do mês: o que cada profissional tem a receber, descontando os vales */
export default function FinanceCommissions({ month, rows, advances, professionals, methods, defaultDate, errors }: Props) {
    const [showAdvance, setShowAdvance] = useState(false);
    const remaining = rows.reduce((total, row) => total + Math.max(row.remaining, 0), 0);

    return (
        <Page
            title="Comissões"
            eyebrow="Financeiro"
            subtitle="Calculadas sobre os atendimentos pagos, com o % de cada profissional."
            actions={
                <>
                    <MonthNav month={month} routeName="finance.commissions" />
                    <button
                        type="button"
                        onClick={() => setShowAdvance(true)}
                        className="inline-flex h-11 items-center justify-center rounded-campo border border-linha bg-cartao px-4 text-sm font-semibold hover:bg-areia"
                    >
                        Lançar vale
                    </button>
                </>
            }
        >
            <FinanceTabs month={month} />

            <div className="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-3">
                <Stat label="Comissões do mês" value={money(sum(rows, 'commission'))} />
                <Stat label="Vales já adiantados" value={money(sum(rows, 'advance'))} />
                <Stat label="Falta pagar" value={money(remaining)} dark />
            </div>

            {showAdvance && (
                <AdvanceForm professionals={professionals} methods={methods} defaultDate={defaultDate} onClose={() => setShowAdvance(false)} />
            )}

            <InputError message={errors.payout} />

            <section aria-label="Comissões por profissional" className="cartao overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full min-w-[860px] border-collapse text-sm [&_td]:whitespace-nowrap">
                        <thead>
                            <tr className="bg-areia text-left text-xs uppercase tracking-wider text-suave">
                                <th className="px-4 py-3 font-semibold">Profissional</th>
                                <th className="px-2 py-3 text-right font-semibold">Atendimentos</th>
                                <th className="px-2 py-3 text-right font-semibold">Faturado</th>
                                <th className="px-2 py-3 text-right font-semibold">%</th>
                                <th className="px-2 py-3 text-right font-semibold">Comissão</th>
                                <th className="px-2 py-3 text-right font-semibold">Vales</th>
                                <th className="px-2 py-3 text-right font-semibold">A pagar</th>
                                <th className="px-4 py-3">
                                    <span className="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows.length > 0 ? (
                                rows.map((row) => <CommissionRow key={row.professional.id} row={row} month={month.value} />)
                            ) : (
                                <tr>
                                    <td colSpan={8} className="px-4 py-8 text-center text-suave">
                                        Nenhum profissional ativo.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                        {rows.length > 0 && (
                            <tfoot>
                                <tr className="border-t-2 border-linha font-bold">
                                    <td className="px-4 py-3">Total</td>
                                    <td className="px-2 py-3 text-right">{sum(rows, 'count')}</td>
                                    <td className="px-2 py-3 text-right">{money(sum(rows, 'billed'))}</td>
                                    <td />
                                    <td className="px-2 py-3 text-right">{money(sum(rows, 'commission'))}</td>
                                    <td className="px-2 py-3 text-right">{money(sum(rows, 'advance'))}</td>
                                    <td className="px-2 py-3 text-right">{money(sum(rows, 'due'))}</td>
                                    <td />
                                </tr>
                            </tfoot>
                        )}
                    </table>
                </div>
            </section>

            <section className="cartao flex max-w-[560px] flex-col gap-1 p-5">
                <h2 className="mb-2 text-xl font-semibold">Vales do mês</h2>
                {advances.length > 0 ? (
                    advances.map((advance) => (
                        <div key={advance.id} className="flex min-h-12 items-center gap-3 border-b border-linha-clara text-sm last:border-b-0">
                            <span className="w-12 text-suave">{advance.date}</span>
                            <span className="min-w-0 flex-1 truncate font-semibold">{advance.professional}</span>
                            <span className="text-suave">{advance.method}</span>
                            <span className="w-[100px] text-right font-semibold">{money(advance.amount)}</span>
                            <button
                                type="button"
                                onClick={() => window.confirm('Remover este vale?') && router.delete(route('finance.advances.destroy', advance.id), { preserveScroll: true })}
                                className="flex h-10 w-10 items-center justify-center rounded-full text-suave hover:bg-areia hover:text-perigo"
                            >
                                <span className="sr-only">Remover vale de {advance.professional}</span>
                                <Icon name="fechar" className="h-4 w-4" />
                            </button>
                        </div>
                    ))
                ) : (
                    <p className="text-sm text-suave">Nenhum vale lançado neste mês.</p>
                )}
            </section>
        </Page>
    );
}

function CommissionRow({ row, month }: { row: Row; month: string }) {
    const { professional } = row;
    const options = { preserveScroll: true };

    return (
        <tr className="border-t border-linha-clara">
            <td className="px-4 py-3">
                <span className="flex items-center gap-2.5">
                    <Avatar name={professional.name} size="h-9 w-9 text-xs" />
                    <span className="font-semibold">{professional.name}</span>
                </span>
            </td>
            <td className="px-2 py-3 text-right">{row.count}</td>
            <td className="px-2 py-3 text-right">{money(row.billed)}</td>
            <td className="px-2 py-3 text-right">{professional.percent}</td>
            <td className="px-2 py-3 text-right">{money(row.commission)}</td>
            <td className="px-2 py-3 text-right text-suave">{row.advance > 0 ? `− ${money(row.advance)}` : '—'}</td>
            <td className="px-2 py-3 text-right font-bold">
                {row.due < 0 ? '− ' : ''}
                {money(Math.abs(row.due))}
            </td>
            <td className="whitespace-nowrap px-4 py-2 text-right">
                {row.remaining > 0 ? (
                    <button
                        type="button"
                        onClick={() => router.post(route('finance.commissions.pay', professional.id), { mes: month }, options)}
                        className="min-h-10 rounded-campo bg-cafe px-3.5 text-[13px] font-semibold text-white hover:bg-cafe-escuro"
                    >
                        {row.paid > 0 ? `Pagar o restante (${money(row.remaining)})` : 'Marcar como pago'}
                    </button>
                ) : row.paid > 0 ? (
                    <span className="inline-flex items-center gap-2">
                        <span className="selo border-transparent bg-[#E7F4EC] text-[#1C5234]">
                            <Icon name="check" className="h-3.5 w-3.5" />
                            Pago
                        </span>
                        <button
                            type="button"
                            onClick={() =>
                                window.confirm(`Desfazer o pagamento da comissão de ${professional.name}?`) &&
                                router.delete(route('finance.commissions.unpay', professional.id), { data: { mes: month }, ...options })
                            }
                            className="min-h-10 px-1 text-[13px] font-semibold text-suave hover:text-cafe"
                        >
                            Desfazer
                        </button>
                    </span>
                ) : (
                    <span className="text-suave">—</span>
                )}
            </td>
        </tr>
    );
}

/** Formulário de vale: abre pelo botão "Lançar vale" */
function AdvanceForm({ professionals, methods, defaultDate, onClose }: Pick<Props, 'professionals' | 'methods' | 'defaultDate'> & { onClose: () => void }) {
    const amount = useRef<HTMLInputElement>(null);
    const form = useForm({ professional_id: String(professionals[0]?.id ?? ''), amount: '', method: methods[0]?.value ?? '', given_on: defaultDate });
    const { data, setData, errors } = form;

    useEffect(() => amount.current?.focus(), []);

    return (
        <section className="cartao p-5">
            <form
                className="flex flex-col gap-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('finance.advances.store'), { errorBag: 'advance', preserveScroll: true, onSuccess: onClose });
                }}
            >
                <h2 className="text-xl font-semibold">Lançar vale</h2>
                <div className="grid grid-cols-[repeat(auto-fit,minmax(160px,1fr))] gap-3">
                    <div>
                        <InputLabel htmlFor="professional_id" value="Profissional" />
                        <SelectInput id="professional_id" value={data.professional_id} onChange={(e) => setData('professional_id', e.target.value)} required>
                            {professionals.map((professional) => (
                                <option key={professional.id} value={professional.id}>
                                    {professional.name}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError message={errors.professional_id} className="mt-1.5" />
                    </div>
                    <div>
                        <InputLabel htmlFor="advance_amount" value="Valor (R$)" />
                        <TextInput id="advance_amount" ref={amount} inputMode="decimal" placeholder="0,00" value={data.amount} onChange={(e) => setData('amount', e.target.value)} required />
                        <InputError message={errors.amount} className="mt-1.5" />
                    </div>
                    <div>
                        <InputLabel htmlFor="advance_method" value="Forma" />
                        <SelectInput id="advance_method" value={data.method} onChange={(e) => setData('method', e.target.value)} required>
                            {methods.map((method) => (
                                <option key={method.value} value={method.value}>
                                    {method.label}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError message={errors.method} className="mt-1.5" />
                    </div>
                    <div>
                        <InputLabel htmlFor="given_on" value="Data" />
                        <TextInput id="given_on" type="date" value={data.given_on} onChange={(e) => setData('given_on', e.target.value)} required />
                        <InputError message={errors.given_on} className="mt-1.5" />
                    </div>
                </div>
                <div className="flex gap-2">
                    <PrimaryButton disabled={form.processing}>Salvar vale</PrimaryButton>
                    <button type="button" onClick={onClose} className="h-11 rounded-campo px-4 text-sm font-semibold hover:bg-areia">
                        Cancelar
                    </button>
                </div>
            </form>
        </section>
    );
}
