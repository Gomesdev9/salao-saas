import { Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Avatar from '@/components/Avatar';
import { InputError, InputLabel, SelectInput, TextInput } from '@/components/Form';
import Icon from '@/components/Icon';
import Page from '@/layouts/Page';
import { cx, money, parseMoney } from '@/lib/format';
import type { Option } from '@/types';

type Props = {
    appointment: { id: number; client: string; when: string; service: string; duration: number; professional: string; price: number };
    commission: { percent: number; label: string };
    methods: Option[];
    /** Descontos oferecidos, em % */
    discounts: number[];
    pixKey: string | null;
};

/**
 * Receber pagamento: recalcula total, troco e comissão enquanto a pessoa escolhe desconto e forma.
 * Os valores de verdade são calculados de novo no servidor (PaymentController) ao confirmar.
 */
export default function PaymentCreate({ appointment, commission, methods, discounts, pixKey }: Props) {
    const form = useForm({ method: 'pix', discount_percent: 0, cash_received: '', installments: '1' });
    const { data, setData, errors } = form;

    const subtotal = appointment.price;
    const total = Math.round(subtotal * (100 - data.discount_percent)) / 100;
    const received = parseMoney(data.cash_received);
    const change = received === null ? null : received - total;
    const commissionValue = Math.round(total * commission.percent) / 100;
    const methodLabel = methods.find((method) => method.value === data.method)?.label;

    return (
        <Page title="Receber pagamento" back={route('appointments.edit', appointment.id)} backLabel="Voltar para o agendamento">
            <form
                className="flex flex-wrap items-start gap-5"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('appointments.payment.store', appointment.id));
                }}
            >
                <div className="flex min-w-0 flex-[2_1_440px] flex-col gap-5">
                    <section className="cartao flex flex-col gap-3 p-5">
                        <h2 className="text-xl font-semibold">O que foi feito</h2>
                        <div className="flex items-center gap-3 rounded-campo bg-areia px-3.5 py-3">
                            <span className="flex flex-1 flex-col gap-0.5">
                                <span className="text-[15px] font-semibold">{appointment.service}</span>
                                <span className="text-[13px] text-suave">
                                    {appointment.duration} min · com {appointment.professional}
                                </span>
                            </span>
                            <span className="text-[15px] font-bold">{money(subtotal)}</span>
                        </div>
                    </section>

                    <fieldset className="cartao flex flex-col gap-3 p-5">
                        <legend className="float-left mb-3 w-full font-display text-xl font-semibold">Desconto</legend>
                        <div className="flex flex-wrap gap-2">
                            {discounts.map((discount) => (
                                <label key={discount} className="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="discount_percent"
                                        value={discount}
                                        checked={data.discount_percent === discount}
                                        onChange={() => setData('discount_percent', discount)}
                                        className="peer sr-only"
                                    />
                                    <span className="inline-flex min-h-11 items-center rounded-full border border-linha bg-cartao px-4 text-sm font-semibold hover:bg-areia peer-checked:border-tinta peer-checked:bg-tinta peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-caramelo">
                                        {discount ? `${discount}%` : 'Sem desconto'}
                                    </span>
                                </label>
                            ))}
                        </div>
                        <InputError message={errors.discount_percent} />
                    </fieldset>

                    <fieldset className="cartao flex flex-col gap-3.5 p-5">
                        <legend className="float-left mb-3.5 w-full font-display text-xl font-semibold">Forma de pagamento</legend>
                        <div className="grid grid-cols-[repeat(auto-fit,minmax(120px,1fr))] gap-2.5">
                            {methods.map((method) => (
                                <label key={method.value} className="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="method"
                                        value={method.value}
                                        checked={data.method === method.value}
                                        onChange={() => setData('method', method.value)}
                                        className="peer sr-only"
                                    />
                                    <span className="flex min-h-16 items-center justify-center rounded-2xl border-2 border-linha bg-cartao text-[15px] font-bold hover:bg-areia peer-checked:border-cafe peer-checked:bg-pele peer-checked:text-cafe-escuro peer-focus-visible:ring-2 peer-focus-visible:ring-caramelo">
                                        {method.label}
                                    </span>
                                </label>
                            ))}
                        </div>
                        <InputError message={errors.method} />

                        {data.method === 'cash' && (
                            <div className="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-3">
                                <div>
                                    <InputLabel htmlFor="cash_received" value="Valor recebido" />
                                    <TextInput id="cash_received" inputMode="decimal" placeholder="0,00" value={data.cash_received} onChange={(e) => setData('cash_received', e.target.value)} />
                                    <InputError message={errors.cash_received} className="mt-1.5" />
                                </div>
                                <div>
                                    <span className="mb-1.5 block text-sm font-semibold">Troco</span>
                                    <span
                                        className={cx(
                                            'flex h-11 items-center rounded-campo px-3 text-[17px] font-bold',
                                            change === null || change >= 0 ? 'bg-[#E7F4EC] text-[#1C5234]' : 'bg-[#FBE9E7] text-[#8A2A1B]',
                                        )}
                                    >
                                        {change === null ? '—' : change >= 0 ? money(change) : `Faltam ${money(-change)}`}
                                    </span>
                                </div>
                            </div>
                        )}

                        {data.method === 'credit' && (
                            <div className="max-w-[260px]">
                                <InputLabel htmlFor="installments" value="Parcelas" />
                                <SelectInput id="installments" value={data.installments} onChange={(e) => setData('installments', e.target.value)}>
                                    <option value="1">À vista</option>
                                    <option value="2">2x sem juros</option>
                                    <option value="3">3x sem juros</option>
                                </SelectInput>
                            </div>
                        )}

                        {data.method === 'pix' && (
                            <div className="flex flex-wrap items-center gap-3 rounded-campo bg-areia px-3.5 py-3 text-sm">
                                {pixKey ? (
                                    <>
                                        <span className="min-w-0 flex-1 break-all">
                                            Chave Pix da empresa: <strong>{pixKey}</strong>
                                        </span>
                                        <CopyPixKey pixKey={pixKey} />
                                    </>
                                ) : (
                                    <>
                                        <span className="flex-1">A chave Pix da empresa ainda não foi cadastrada.</span>
                                        <Link href={`${route('profile.edit')}#empresa`} className="font-semibold text-cafe hover:text-cafe-escuro">
                                            Cadastrar chave Pix
                                        </Link>
                                    </>
                                )}
                            </div>
                        )}
                    </fieldset>
                </div>

                <aside aria-label="Resumo" className="cartao flex min-w-0 flex-[1_1_280px] flex-col gap-3.5 p-5">
                    <div className="flex items-center gap-3">
                        <Avatar name={appointment.client} size="h-11 w-11 text-[15px]" />
                        <div className="flex flex-col">
                            <span className="text-base font-bold">{appointment.client}</span>
                            <span className="text-[13px] text-suave">{appointment.when}</span>
                        </div>
                    </div>
                    <dl className="flex flex-col gap-2 border-y border-linha-clara py-3.5 text-sm">
                        <div className="flex justify-between">
                            <dt className="text-suave">Subtotal</dt>
                            <dd>{money(subtotal)}</dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-suave">Desconto</dt>
                            <dd>{data.discount_percent ? `− ${money(subtotal - total)}` : money(0)}</dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-suave">Forma</dt>
                            <dd>{methodLabel}</dd>
                        </div>
                    </dl>
                    <div className="flex items-baseline justify-between">
                        <span className="text-[15px] font-semibold">Total</span>
                        <span className="font-display text-[32px] font-bold">{money(total)}</span>
                    </div>
                    <span className="text-[13px] text-suave">
                        Comissão de {appointment.professional} ({commission.label}): {money(commissionValue)}
                    </span>
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="flex h-12 items-center justify-center gap-2 rounded-campo bg-cafe text-[15px] font-bold text-white hover:bg-cafe-escuro focus:outline-none focus:ring-2 focus:ring-caramelo focus:ring-offset-2 disabled:opacity-50"
                    >
                        <Icon name="check" className="h-[18px] w-[18px]" />
                        Confirmar pagamento
                    </button>
                    <span className="text-[13px] leading-5 text-suave">O horário fica como “Atendido” e o valor entra nos lançamentos do dia.</span>
                </aside>
            </form>
        </Page>
    );
}

function CopyPixKey({ pixKey }: { pixKey: string }) {
    const [copied, setCopied] = useState(false);

    return (
        <button
            type="button"
            onClick={() => {
                navigator.clipboard.writeText(pixKey);
                setCopied(true);
                setTimeout(() => setCopied(false), 2000);
            }}
            className="min-h-10 rounded-campo border border-linha-forte bg-cartao px-3.5 text-[13px] font-semibold hover:bg-creme"
        >
            {copied ? 'Copiada!' : 'Copiar chave'}
        </button>
    );
}
