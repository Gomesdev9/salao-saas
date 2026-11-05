import { Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { PrimaryButton } from '@/components/Buttons';
import Chip from '@/components/Chip';
import FinanceTabs from '@/components/FinanceTabs';
import { Checkbox, InputError, InputLabel, SelectInput, TextInput } from '@/components/Form';
import Icon from '@/components/Icon';
import MonthNav, { type Month } from '@/components/MonthNav';
import Pagination, { type Paginated } from '@/components/Pagination';
import Page from '@/layouts/Page';
import { cx, money, plural } from '@/lib/format';
import type { Option } from '@/types';

type Row = {
    key: string;
    date: string;
    description: string;
    detail: string | null;
    method: string;
    /** Positivo é entrada; negativo é saída */
    amount: number;
    /** Nome da situação e as cores do selo */
    status: [string, string];
    appointmentId: number | null;
    expense: { id: number; paid: boolean } | null;
};

type Props = {
    month: Month;
    monthName: string;
    type: 'entradas' | 'saidas' | null;
    search: string;
    rows: Paginated<Row>;
    incoming: number;
    outgoing: number;
    categories: string[];
    methods: Option[];
    defaultDue: string;
};

const signed = (value: number) => `${value < 0 ? '− ' : ''}${money(Math.abs(value))}`;

/** Lançamentos do mês: entradas (atendimentos pagos) e saídas (despesas), com filtro e busca */
export default function FinanceEntries({ month, monthName, type, search, rows, incoming, outgoing, categories, methods, defaultDue }: Props) {
    const [query, setQuery] = useState(search);
    const filters = [
        [null, 'Todos'],
        ['entradas', 'Entradas'],
        ['saidas', 'Saídas'],
    ] as const;
    const params = (extra: Record<string, string | null>) =>
        Object.fromEntries(Object.entries({ mes: month.value, ...extra }).filter(([, value]) => value)) as Record<string, string>;

    return (
        <Page title="Lançamentos" eyebrow="Financeiro" subtitle={`Tudo o que entrou e saiu do caixa em ${monthName}.`} actions={<MonthNav month={month} routeName="finance.entries" />}>
            <FinanceTabs month={month} />

            <div className="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-3">
                <div className="cartao flex flex-col gap-1 px-5 py-4">
                    <span className="text-[13px] text-suave">Entradas</span>
                    <span className="text-[22px] font-bold text-[#1C5234]">+ {money(incoming)}</span>
                </div>
                <div className="cartao flex flex-col gap-1 px-5 py-4">
                    <span className="text-[13px] text-suave">Saídas</span>
                    <span className="text-[22px] font-bold text-[#8A2A1B]">− {money(outgoing)}</span>
                </div>
                <div className="cartao flex flex-col gap-1 px-5 py-4">
                    <span className="text-[13px] text-suave">Saldo do mês</span>
                    <span className="text-[22px] font-bold">{signed(incoming - outgoing)}</span>
                </div>
            </div>

            <div className="flex flex-wrap items-start gap-5">
                <section aria-label="Lançamentos do mês" className="cartao min-w-0 flex-[2_1_520px] overflow-hidden">
                    <div className="flex flex-wrap items-center gap-2.5 border-b border-linha px-4 py-3.5">
                        {filters.map(([value, label]) => (
                            <Chip key={label} href={route('finance.entries', params({ tipo: value, busca: search }))} on={type === value}>
                                {label}
                            </Chip>
                        ))}
                        <form
                            role="search"
                            className="ml-auto min-w-[200px] flex-[0_1_260px]"
                            onSubmit={(event) => {
                                event.preventDefault();
                                router.get(route('finance.entries'), params({ tipo: type, busca: query.trim() }), { preserveState: true });
                            }}
                        >
                            <label className="flex h-10 items-center gap-2 rounded-campo border border-linha-forte bg-cartao px-3 text-suave focus-within:border-caramelo">
                                <Icon name="busca" className="h-4 w-4" />
                                <span className="sr-only">Buscar lançamento</span>
                                <input
                                    type="search"
                                    name="busca"
                                    value={query}
                                    onChange={(e) => setQuery(e.target.value)}
                                    placeholder="Buscar"
                                    className="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-tinta placeholder:text-suave/60 focus:ring-0"
                                />
                            </label>
                        </form>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[640px] border-collapse text-sm">
                            <thead>
                                <tr className="bg-areia text-left text-xs uppercase tracking-wider text-suave">
                                    <th className="px-4 py-2.5 font-semibold">Data</th>
                                    <th className="min-w-[190px] px-3 py-2.5 font-semibold">Descrição</th>
                                    <th className="px-3 py-2.5 font-semibold">Forma</th>
                                    <th className="px-3 py-2.5 text-right font-semibold">Valor</th>
                                    <th className="px-3 py-2.5 font-semibold">Situação</th>
                                    <th className="px-2 py-2.5">
                                        <span className="sr-only">Ações</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.data.length > 0 ? (
                                    rows.data.map((row) => <EntryRow key={row.key} row={row} />)
                                ) : (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-8 text-center text-suave">
                                            {search !== '' ? `Nada encontrado para “${search}”.` : `Nenhum lançamento em ${monthName}.`}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    <div className="flex flex-wrap items-center justify-between gap-3 border-t border-linha px-4 py-3 text-[13px] text-suave">
                        <span>
                            {rows.total > rows.data.length ? `Mostrando ${rows.from}–${rows.to} de ${rows.total} lançamentos` : plural(rows.total, 'lançamento', 'lançamentos')}{' '}
                            {search !== '' ? `com “${search}” ` : ''}em {monthName}
                        </span>
                        <Pagination page={rows} />
                    </div>
                </section>

                <ExpenseForm categories={categories} methods={methods} defaultDue={defaultDue} />
            </div>
        </Page>
    );
}

function EntryRow({ row }: { row: Row }) {
    const expense = row.expense;

    return (
        <tr className="border-t border-linha-clara">
            <td className="whitespace-nowrap px-4 py-3 text-suave">{row.date}</td>
            <td className="px-3 py-3">
                <span className="flex flex-col gap-0.5">
                    {row.appointmentId ? (
                        <Link href={route('appointments.edit', row.appointmentId)} className="font-semibold text-tinta hover:text-cafe">
                            {row.description}
                        </Link>
                    ) : (
                        <span className="font-semibold">{row.description}</span>
                    )}
                    <span className="text-xs text-suave">{row.detail}</span>
                </span>
            </td>
            <td className="whitespace-nowrap px-3 py-3">{row.method}</td>
            <td className={cx('whitespace-nowrap px-3 py-3 text-right font-bold', row.amount > 0 ? 'text-[#1C5234]' : 'text-[#8A2A1B]')}>
                {row.amount > 0 ? '+' : '−'} {money(Math.abs(row.amount))}
            </td>
            <td className="px-3 py-3">
                <span className={cx('selo border-transparent', row.status[1])}>{row.status[0]}</span>
            </td>
            <td className="whitespace-nowrap px-2 py-1.5 text-right">
                {expense && (
                    <div className="flex items-center justify-end">
                        {!expense.paid && (
                            <button
                                type="button"
                                title="Marcar como paga"
                                onClick={() => router.patch(route('finance.expenses.pay', expense.id), {}, { preserveScroll: true })}
                                className="flex h-10 w-10 items-center justify-center rounded-full text-cafe hover:bg-areia"
                            >
                                <span className="sr-only">Marcar {row.description} como paga</span>
                                <Icon name="check" className="h-[18px] w-[18px]" />
                            </button>
                        )}
                        <button
                            type="button"
                            title="Remover"
                            onClick={() => window.confirm(`Remover a despesa ${row.description}?`) && router.delete(route('finance.expenses.destroy', expense.id), { preserveScroll: true })}
                            className="flex h-10 w-10 items-center justify-center rounded-full text-suave hover:bg-areia hover:text-perigo"
                        >
                            <span className="sr-only">Remover {row.description}</span>
                            <Icon name="fechar" className="h-4 w-4" />
                        </button>
                    </div>
                )}
            </td>
        </tr>
    );
}

function ExpenseForm({ categories, methods, defaultDue }: Pick<Props, 'categories' | 'methods' | 'defaultDue'>) {
    const form = useForm({ description: '', category: categories[0], amount: '', due_date: defaultDue, method: '', paid: false });
    const { data, setData, errors } = form;

    return (
        <form
            id="nova-despesa"
            aria-labelledby="titulo-nova-despesa"
            className="cartao flex min-w-0 flex-[1_1_280px] scroll-mt-6 flex-col gap-3.5 p-5"
            onSubmit={(event) => {
                event.preventDefault();
                form.post(route('finance.expenses.store'), { preserveScroll: true, onSuccess: () => form.reset('description', 'amount', 'paid') });
            }}
        >
            <h2 id="titulo-nova-despesa" className="text-xl font-semibold">
                Nova despesa
            </h2>

            <div>
                <InputLabel htmlFor="description" value="Descrição" />
                <TextInput id="description" value={data.description} onChange={(e) => setData('description', e.target.value)} placeholder="Ex.: Conta de luz" required maxLength={120} />
                <InputError message={errors.description} className="mt-1.5" />
            </div>

            <div>
                <InputLabel htmlFor="category" value="Categoria" />
                <SelectInput id="category" value={data.category} onChange={(e) => setData('category', e.target.value)} required>
                    {categories.map((category) => (
                        <option key={category} value={category}>
                            {category}
                        </option>
                    ))}
                </SelectInput>
                <InputError message={errors.category} className="mt-1.5" />
            </div>

            <div className="grid grid-cols-2 gap-3">
                <div className="min-w-0">
                    <InputLabel htmlFor="amount" value="Valor (R$)" />
                    <TextInput id="amount" inputMode="decimal" value={data.amount} onChange={(e) => setData('amount', e.target.value)} placeholder="0,00" required />
                    <InputError message={errors.amount} className="mt-1.5" />
                </div>
                <div className="min-w-0">
                    <InputLabel htmlFor="due_date" value="Vencimento" />
                    <TextInput id="due_date" type="date" value={data.due_date} onChange={(e) => setData('due_date', e.target.value)} required />
                    <InputError message={errors.due_date} className="mt-1.5" />
                </div>
            </div>

            <div>
                <InputLabel htmlFor="method" value="Forma de pagamento" />
                <SelectInput id="method" value={data.method} onChange={(e) => setData('method', e.target.value)}>
                    <option value="">—</option>
                    {methods.map((method) => (
                        <option key={method.value} value={method.value}>
                            {method.label}
                        </option>
                    ))}
                </SelectInput>
                <InputError message={errors.method} className="mt-1.5" />
            </div>

            <Checkbox label="Já está pago" checked={data.paid} onChange={(e) => setData('paid', e.target.checked)} />

            <PrimaryButton disabled={form.processing}>Salvar despesa</PrimaryButton>
            <span className="text-[13px] leading-5 text-suave">As entradas vêm sozinhas: cada atendimento pago na agenda vira um lançamento.</span>
        </form>
    );
}
