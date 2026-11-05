import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { LinkButton } from '@/components/Buttons';
import Icon from '@/components/Icon';
import Selo from '@/components/Selo';
import Stat from '@/components/Stat';
import Page from '@/layouts/Page';
import { cx, money } from '@/lib/format';
import type { Status } from '@/types';

type Props = {
    greeting: string;
    firstName: string;
    today: string;
    todayCount: number;
    doneCount: number;
    expected: number;
    toConfirm: number;
    next: { id: number; time: string; client: string; service: string; professional: string; status: Status }[];
    birthdays: { id: number; name: string; whatsapp: string | null; date: string }[];
    booking: { available: boolean; url: string; trialOnly: boolean; plan: string };
    professionalsCount: number;
    servicesCount: number;
    clientsCount: number;
    /** Primeiros passos de uma conta nova. O quadro some quando todos estiverem feitos. */
    firstSteps: { label: string; route: string; done: boolean }[];
};

export default function Dashboard(props: Props) {
    const { salon } = usePage().props.auth!;
    const { todayCount, doneCount, firstSteps } = props;

    return (
        <Page
            title={`${props.greeting}, ${props.firstName}`}
            eyebrow={salon.name}
            subtitle={props.today}
            actions={
                <>
                    <LinkButton variant="secondary" href={route('clients.create')}>
                        Novo cliente
                    </LinkButton>
                    <LinkButton href={route('appointments.create')} icon="mais">
                        Novo agendamento
                    </LinkButton>
                </>
            }
        >
            {firstSteps.some((step) => !step.done) && (
                <section className="cartao flex flex-col gap-4 px-5 py-5 sm:px-6">
                    <div className="flex flex-col gap-1">
                        <h2 className="text-xl font-bold">Primeiros passos</h2>
                        <p className="text-sm text-suave">Deixe o sistema pronto para a sua empresa. Este quadro some quando tudo estiver feito.</p>
                    </div>
                    <ol className="grid gap-3 md:grid-cols-3">
                        {firstSteps.map((step, i) => (
                            <li key={step.route}>
                                <Link
                                    href={route(step.route)}
                                    className={cx(
                                        'flex min-h-14 items-center gap-3 rounded-campo border px-4 py-3 text-sm font-semibold transition',
                                        step.done ? 'border-[#3E9A63]/40 bg-[#E7F4EC] text-[#1C5234]' : 'border-linha bg-creme hover:border-caramelo',
                                    )}
                                >
                                    <span className={cx('flex h-8 w-8 shrink-0 items-center justify-center rounded-full', step.done ? 'bg-[#3E9A63] text-white' : 'bg-pele text-cafe-escuro')}>
                                        {step.done ? (
                                            <>
                                                <Icon name="check" className="h-4 w-4" />
                                                <span className="sr-only">Feito:</span>
                                            </>
                                        ) : (
                                            i + 1
                                        )}
                                    </span>
                                    {step.label}
                                </Link>
                            </li>
                        ))}
                    </ol>
                </section>
            )}

            <div className="grid gap-3 sm:grid-cols-3">
                <Stat highlight href={route('appointments.index')} label="Atendimentos hoje" value={todayCount} hint={`${doneCount} já atendidos · ${todayCount - doneCount} a seguir`} />
                <Stat href={route('appointments.index')} label="Previsto hoje" value={money(props.expected)} hint="Soma dos horários não cancelados" />
                <Stat href={route('appointments.index')} label="A confirmar" value={props.toConfirm} hint="Horários ainda como “Agendado”" />
            </div>

            <div className="flex flex-wrap items-start gap-5">
                <section className="cartao min-w-0 flex-[2_1_420px]">
                    <div className="flex items-center justify-between border-b border-linha px-5 py-4">
                        <h2 className="text-[17px] font-bold">Próximos horários</h2>
                        <Link href={route('appointments.index')} className="text-sm font-semibold text-cafe hover:text-cafe-escuro">
                            Ver agenda
                        </Link>
                    </div>
                    {props.next.length > 0 ? (
                        props.next.map((appointment) => (
                            <Link
                                key={appointment.id}
                                href={route('appointments.edit', appointment.id)}
                                className="flex items-center gap-4 border-b border-linha-clara px-5 py-3.5 last:border-b-0 hover:bg-areia/60"
                            >
                                <span className="w-[52px] text-[15px] font-bold">{appointment.time}</span>
                                <span className="flex min-w-0 flex-1 flex-col">
                                    <span className="truncate text-sm font-semibold">{appointment.client}</span>
                                    <span className="truncate text-[13px] text-suave">
                                        {appointment.service} com {appointment.professional}
                                    </span>
                                </span>
                                <Selo color={appointment.status.color}>{appointment.status.label}</Selo>
                            </Link>
                        ))
                    ) : (
                        <p className="px-5 py-8 text-center text-sm text-suave">Nenhum horário pela frente hoje.</p>
                    )}
                </section>

                <div className="flex min-w-0 flex-[1_1_280px] flex-col gap-5">
                    <BookingLink {...props.booking} />

                    <section className="cartao flex flex-col gap-3.5 px-5 py-4">
                        <h2 className="text-[17px] font-bold">Aniversariantes da semana</h2>
                        {props.birthdays.length > 0 ? (
                            props.birthdays.map((client) => (
                                <Link key={client.id} href={route('clients.edit', client.id)} className="flex items-center gap-3">
                                    <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-mel text-xs font-bold text-mel-texto">{client.date}</span>
                                    <span className="flex min-w-0 flex-col">
                                        <span className="truncate text-sm font-semibold">{client.name}</span>
                                        <span className="text-[13px] text-suave">{client.whatsapp || 'Sem WhatsApp'}</span>
                                    </span>
                                </Link>
                            ))
                        ) : (
                            <p className="text-sm text-suave">Nenhum aniversário nos próximos 7 dias.</p>
                        )}
                    </section>

                    <section className="cartao flex flex-col px-5 py-4">
                        <h2 className="mb-1.5 text-[17px] font-bold">Cadastros</h2>
                        {(
                            [
                                ['professionals.index', 'Profissionais ativos', props.professionalsCount],
                                ['services.index', 'Serviços ativos', props.servicesCount],
                                ['clients.index', 'Clientes', props.clientsCount],
                            ] as const
                        ).map(([name, label, count]) => (
                            <Link key={name} href={route(name)} className="flex min-h-11 items-center justify-between border-b border-linha-clara text-sm last:border-b-0 hover:text-cafe">
                                <span>{label}</span>
                                <strong>{count}</strong>
                            </Link>
                        ))}
                    </section>
                </div>
            </div>
        </Page>
    );
}

/** Link da página em que a cliente agenda sozinha */
function BookingLink({ available, url, trialOnly, plan }: Props['booking']) {
    const [copied, setCopied] = useState(false);

    const copy = () => {
        navigator.clipboard.writeText(url);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <section className="flex flex-col gap-3 rounded-cartao bg-tinta px-5 py-4 text-creme shadow-menu">
            <div className="flex items-center gap-2.5">
                <span className="flex h-9 w-9 items-center justify-center rounded-full bg-menu-ativo text-[#D9A57F]">
                    <Icon name="link" className="h-[18px] w-[18px]" />
                </span>
                <h2 className="font-sans text-[17px] font-bold">Agendamento online</h2>
            </div>
            {available ? (
                <>
                    <p className="text-sm text-menu-texto">Mande este link para as clientes marcarem sozinhas. Os horários caem direto na agenda como “Agendado”.</p>
                    <span className="truncate rounded-campo bg-menu-ativo px-3 py-2.5 text-[13px] text-menu-texto">{url}</span>
                    <div className="grid grid-cols-2 gap-2">
                        <button type="button" onClick={copy} className="inline-flex h-11 items-center justify-center rounded-full bg-pele text-sm font-semibold text-tinta hover:bg-white">
                            {copied ? 'Copiado!' : 'Copiar link'}
                        </button>
                        <a href={url} target="_blank" rel="noopener" className="inline-flex h-11 items-center justify-center rounded-full border border-menu-suave text-sm font-semibold hover:bg-menu-ativo">
                            Abrir
                        </a>
                    </div>
                    {trialOnly && <p className="text-xs text-menu-suave">No plano {plan}, o link funciona até o fim do teste grátis.</p>}
                </>
            ) : (
                <>
                    <p className="text-sm text-menu-texto">Deixe as clientes marcarem sozinhas por um link da sua empresa. Faz parte dos planos Profissional e Premium.</p>
                    <Link href={route('plan.edit')} className="inline-flex h-11 items-center justify-center rounded-full bg-pele text-sm font-semibold text-tinta hover:bg-white">
                        Ver planos
                    </Link>
                </>
            )}
        </section>
    );
}
