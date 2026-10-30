import { Link, router, useForm } from '@inertiajs/react';
import Avatar from '@/components/Avatar';
import { LinkButton } from '@/components/Buttons';
import FormFooter from '@/components/FormFooter';
import { Field, InputError, SelectInput, TextareaInput, TextInput } from '@/components/Form';
import Icon from '@/components/Icon';
import RemoveButton from '@/components/RemoveButton';
import Selo from '@/components/Selo';
import Page from '@/layouts/Page';
import { cx, money } from '@/lib/format';
import type { Status } from '@/types';

type Props = {
    appointment: {
        id: number | null;
        client_id: number | null;
        professional_id: number | null;
        service_id: number | null;
        date: string;
        time: string;
        status: string | null;
        notes: string | null;
    };
    clients: { id: number; name: string; whatsapp: string | null }[];
    professionals: { id: number; name: string }[];
    services: { id: number; label: string }[];
    statuses: Status[];
    /** Só ao editar: situação, valor e se dá para receber o pagamento */
    summary: { when: string; price: number; end: string; status: Status; canReceive: boolean } | null;
    payment: { amount: string; method: string; installments: number; paidAt: string; discountPercent: number | null; change: string | null } | null;
    client: { id: number; name: string; birthday: string | null; whatsappUrl: string | null; notes: string | null } | null;
    errors: Record<string, string>;
};

/** Novo agendamento e editar agendamento */
export default function AppointmentForm({ appointment, clients, professionals, services, statuses, summary, payment, client, errors: pageErrors }: Props) {
    const editing = appointment.id !== null;
    const [day, month] = appointment.date.split('-').reverse();
    const back = route('appointments.index', { data: appointment.date });
    const form = useForm({
        client_id: String(appointment.client_id ?? ''),
        professional_id: String(appointment.professional_id ?? ''),
        service_id: String(appointment.service_id ?? ''),
        date: appointment.date,
        time: appointment.time,
        status: appointment.status ?? '',
        notes: appointment.notes ?? '',
    });
    const { data, setData, errors } = form;

    const fields = (
        <>
            <div className="flex flex-col gap-[18px] p-6">
                {clients.length === 0 && (
                    <div className="rounded-campo bg-mel px-4 py-3 text-sm text-mel-texto">
                        Nenhum cliente cadastrado.{' '}
                        <Link href={route('clients.create')} className="font-semibold underline">
                            Cadastre o cliente
                        </Link>{' '}
                        primeiro.
                    </div>
                )}

                <Field id="client_id" label="Cliente" error={errors.client_id}>
                    <SelectInput id="client_id" value={data.client_id} onChange={(e) => setData('client_id', e.target.value)} required>
                        <option value="">Selecione...</option>
                        {clients.map((option) => (
                            <option key={option.id} value={option.id}>
                                {option.name}
                                {option.whatsapp ? ` — ${option.whatsapp}` : ''}
                            </option>
                        ))}
                    </SelectInput>
                </Field>

                <div className="grid gap-4 sm:grid-cols-2">
                    <Field id="professional_id" label="Profissional" error={errors.professional_id}>
                        <SelectInput id="professional_id" value={data.professional_id} onChange={(e) => setData('professional_id', e.target.value)} required>
                            <option value="">Selecione...</option>
                            {professionals.map((option) => (
                                <option key={option.id} value={option.id}>
                                    {option.name}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>
                    <Field id="service_id" label="Serviço" error={errors.service_id}>
                        <SelectInput id="service_id" value={data.service_id} onChange={(e) => setData('service_id', e.target.value)} required>
                            <option value="">Selecione...</option>
                            {services.map((option) => (
                                <option key={option.id} value={option.id}>
                                    {option.label}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>
                </div>

                <div className="grid gap-4 sm:grid-cols-3">
                    <Field id="date" label="Data" error={errors.date}>
                        <TextInput id="date" type="date" value={data.date} onChange={(e) => setData('date', e.target.value)} required />
                    </Field>
                    <Field id="time" label="Horário" error={errors.time}>
                        <TextInput id="time" type="time" step="300" value={data.time} onChange={(e) => setData('time', e.target.value)} required />
                    </Field>
                    <div>
                        <span className="mb-1.5 block text-sm font-semibold">Término</span>
                        <span className="flex h-11 items-center rounded-campo bg-creme px-3 text-[15px] text-suave">{summary ? `${summary.end} (pela duração)` : 'Pela duração do serviço'}</span>
                    </div>
                </div>

                {editing && (
                    <Field id="status" label="Situação" error={errors.status} className="sm:w-1/3">
                        <SelectInput id="status" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                            {statuses.map((status) => (
                                <option key={status.value} value={status.value}>
                                    {status.label}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>
                )}

                <Field id="notes" label="Observações" error={errors.notes}>
                    <TextareaInput id="notes" rows={3} value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
                </Field>
            </div>

            <FormFooter
                cancel={back}
                processing={form.processing}
                remove={
                    editing && (
                        <RemoveButton href={route('appointments.destroy', appointment.id!)} confirm="Remover este agendamento? Se o cliente só desmarcou, prefira marcar como cancelado.">
                            Remover agendamento
                        </RemoveButton>
                    )
                }
            />
        </>
    );

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        editing ? form.put(route('appointments.update', appointment.id!)) : form.post(route('appointments.store'));
    };

    if (!editing || !summary || !client) {
        return (
            <Page title="Novo agendamento" back={back} backLabel={`Voltar para a agenda de ${day}/${month}`}>
                <form onSubmit={submit} className="cartao max-w-3xl">
                    {fields}
                </form>
            </Page>
        );
    }

    return (
        <Page
            title="Editar agendamento"
            back={back}
            backLabel={`Voltar para a agenda de ${day}/${month}`}
            actions={
                summary.canReceive && (
                    <LinkButton href={route('appointments.payment.create', appointment.id!)} icon="financeiro">
                        Receber pagamento
                    </LinkButton>
                )
            }
        >
            {/* Situação do horário e troca rápida */}
            <section aria-label="Situação do horário" className="cartao flex flex-wrap items-center gap-3 px-5 py-4">
                <div className="mr-auto flex flex-col gap-0.5">
                    <span className="text-[13px] text-suave">
                        {summary.when} · {money(summary.price)}
                    </span>
                    <span className="flex items-center gap-2 text-base font-bold">
                        Situação:{' '}
                        <Selo color={summary.status.color} className="text-[13px]">
                            {summary.status.label}
                        </Selo>
                    </span>
                </div>
                {statuses
                    .filter((status) => status.value !== summary.status.value)
                    .map((status) => (
                        <button
                            key={status.value}
                            type="button"
                            onClick={() => router.patch(route('appointments.status', appointment.id!), { status: status.value }, { preserveScroll: true })}
                            className={cx('min-h-11 rounded-campo border px-3.5 text-[13px] font-semibold', status.color)}
                        >
                            Marcar {status.label.toLowerCase()}
                        </button>
                    ))}
                <InputError message={pageErrors.status} className="w-full" />
                <InputError message={pageErrors.payment} className="w-full" />
            </section>

            {payment && (
                <section aria-label="Pagamento" className="cartao flex flex-wrap items-center gap-3 px-5 py-4">
                    <span className="selo border-transparent bg-[#E7F4EC] text-[13px] text-[#1C5234]">
                        <Icon name="check" className="h-3.5 w-3.5" />
                        Pago
                    </span>
                    <span className="mr-auto text-sm">
                        <strong>{payment.amount}</strong> no {payment.method}
                        {payment.installments > 1 ? ` em ${payment.installments}x` : ''}{' '}
                        <span className="text-suave">
                            · {payment.paidAt}
                            {payment.discountPercent ? ` · ${payment.discountPercent}% de desconto` : ''}
                            {payment.change ? ` · troco de ${payment.change}` : ''}
                        </span>
                    </span>
                    <button
                        type="button"
                        onClick={() =>
                            window.confirm('Desfazer este pagamento? O valor sai dos lançamentos do financeiro.') &&
                            router.delete(route('appointments.payment.destroy', appointment.id!), { preserveScroll: true })
                        }
                        className="min-h-11 rounded-campo px-3 text-[13px] font-semibold text-suave hover:bg-areia hover:text-perigo"
                    >
                        Desfazer pagamento
                    </button>
                </section>
            )}

            <div className="flex flex-wrap items-start gap-5">
                <form onSubmit={submit} className="cartao min-w-0 flex-[2_1_440px]">
                    {fields}
                </form>

                <aside aria-label="Sobre a cliente" className="cartao flex min-w-0 flex-[1_1_260px] flex-col gap-3.5 p-5">
                    <div className="flex items-center gap-3">
                        <Avatar name={client.name} size="h-11 w-11 text-[15px]" />
                        <div className="flex flex-col">
                            <span className="text-base font-bold">{client.name}</span>
                            {client.birthday && <span className="text-[13px] text-suave">Aniversário {client.birthday}</span>}
                        </div>
                    </div>
                    {client.whatsappUrl && (
                        <a
                            href={client.whatsappUrl}
                            target="_blank"
                            rel="noopener"
                            className="inline-flex min-h-11 items-center justify-center gap-2 rounded-campo border border-[#3E9A63] text-sm font-semibold text-[#1C5234] hover:bg-[#E7F4EC]"
                        >
                            <Icon name="whatsapp" className="h-[18px] w-[18px]" />
                            Chamar no WhatsApp
                        </a>
                    )}
                    {client.notes && (
                        <div className="flex flex-col gap-1">
                            <span className="text-[13px] font-semibold text-suave">Observações da cliente</span>
                            <span className="rounded-campo bg-mel px-3 py-2.5 text-sm leading-5 text-mel-texto">{client.notes}</span>
                        </div>
                    )}
                    <Link href={route('clients.edit', client.id)} className="text-sm font-semibold text-cafe hover:text-cafe-escuro">
                        Ver cadastro completo
                    </Link>
                </aside>
            </div>
        </Page>
    );
}
