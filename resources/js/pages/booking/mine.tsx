import { Link, router } from '@inertiajs/react';
import Flash from '@/components/Flash';
import Icon from '@/components/Icon';
import Selo from '@/components/Selo';
import { firstName as first } from '@/lib/format';
import BookingLayout, { type BookingSalon } from '@/layouts/BookingLayout';
import type { Status } from '@/types';

type Upcoming = {
    id: number;
    service: string;
    month: string;
    day: string;
    weekday: string;
    time: string;
    duration: number;
    professional: string;
    status: Status;
    rescheduleUrl: string;
    cancelUrl: string;
};

type Props = {
    salon: BookingSalon;
    firstName: string;
    next: Upcoming | null;
    upcoming: Upcoming[];
    history: { id: number; service: string; date: string; status: Status; againUrl: string }[];
    newUrl: string;
};

const cancel = (url: string) => {
    if (window.confirm('Cancelar este horário?')) {
        router.patch(url, {}, { preserveScroll: true });
    }
};

/** Área da cliente: próximos horários e histórico */
export default function Mine({ salon, firstName, next, upcoming, history, newUrl }: Props) {
    return (
        <BookingLayout
            salon={salon}
            title="Meus horários"
            bar={
                <Link href={newUrl} className="flex h-12 flex-1 items-center justify-center gap-2.5 text-[15px] font-semibold">
                    <span className="flex h-7 w-7 items-center justify-center rounded-full bg-creme text-tinta">
                        <Icon name="mais" className="h-4 w-4" />
                    </span>
                    Novo agendamento
                </Link>
            }
        >
            <div className="flex flex-col gap-1.5">
                <span className="text-xs font-bold uppercase tracking-[0.18em] text-cafe">Área da cliente</span>
                <h1 className="text-[32px] font-medium leading-tight">
                    Olá, <em className="italic text-cafe">{firstName}</em>
                </h1>
            </div>

            <Flash />

            {next ? (
                <section className="flex flex-col gap-4 rounded-cartao bg-tinta p-4 text-creme shadow-menu">
                    <div className="flex items-center justify-between gap-3">
                        <span className="text-[11px] font-bold uppercase tracking-[0.14em] text-menu-suave">Próximo horário</span>
                        <Selo color={next.status.color}>{next.status.label}</Selo>
                    </div>
                    <div className="flex items-center gap-3.5">
                        <span className="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-[16px] bg-menu-ativo">
                            <span className="text-[10px] font-bold uppercase text-menu-suave">{next.month}</span>
                            <span className="text-xl font-bold leading-none">{next.day}</span>
                        </span>
                        <div className="flex min-w-0 flex-col gap-0.5">
                            <span className="truncate text-lg font-bold">{next.service}</span>
                            <span className="text-sm text-menu-texto">
                                {next.weekday} · {next.time} · {next.duration} min
                            </span>
                            <span className="truncate text-[13px] text-menu-suave">Com {next.professional}</span>
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-2.5">
                        <Link href={next.rescheduleUrl} className="inline-flex h-12 items-center justify-center rounded-full bg-pele text-[15px] font-semibold text-tinta hover:bg-white">
                            Remarcar
                        </Link>
                        <button
                            type="button"
                            onClick={() => cancel(next.cancelUrl)}
                            className="inline-flex h-12 w-full items-center justify-center rounded-full border border-menu-suave text-[15px] font-semibold hover:bg-menu-ativo"
                        >
                            Cancelar
                        </button>
                    </div>
                </section>
            ) : (
                <div className="cartao flex flex-col gap-1 p-5">
                    <span className="text-base font-bold">Nenhum horário marcado</span>
                    <span className="text-sm text-suave">Quando você agendar, o horário aparece aqui.</span>
                </div>
            )}

            {upcoming.map((appointment) => (
                <article key={appointment.id} className="cartao flex items-center gap-3.5 p-4">
                    <span className="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-[16px] bg-pele text-cafe-escuro">
                        <span className="text-[10px] font-bold uppercase">{appointment.month}</span>
                        <span className="text-xl font-bold leading-none">{appointment.day}</span>
                    </span>
                    <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                        <span className="truncate text-base font-bold">{appointment.service}</span>
                        <span className="text-[13px] text-suave">
                            {appointment.weekday} · {appointment.time} · {first(appointment.professional)}
                        </span>
                        <span className="flex gap-4 pt-1 text-[13px] font-semibold">
                            <Link href={appointment.rescheduleUrl} className="inline-flex min-h-8 items-center text-cafe underline">
                                Remarcar
                            </Link>
                            <button type="button" onClick={() => cancel(appointment.cancelUrl)} className="inline-flex min-h-8 items-center text-perigo underline">
                                Cancelar
                            </button>
                        </span>
                    </div>
                    <Selo color={appointment.status.color} className="self-start">
                        {appointment.status.label}
                    </Selo>
                </article>
            ))}

            {history.length > 0 && (
                <section className="flex flex-col gap-3 pt-2">
                    <h2 className="text-2xl font-medium">Histórico</h2>
                    <ul className="cartao flex flex-col divide-y divide-linha-clara">
                        {history.map((appointment) => (
                            <li key={appointment.id} className="flex items-center justify-between gap-3 px-4 py-3.5">
                                <span className="flex min-w-0 flex-col gap-1">
                                    <span className="truncate text-[15px] font-bold">{appointment.service}</span>
                                    <span className="flex flex-wrap items-center gap-2 text-[13px] text-suave">
                                        {appointment.date}
                                        <Selo color={appointment.status.color} className="!px-2 !py-0 text-[11px]">
                                            {appointment.status.label}
                                        </Selo>
                                    </span>
                                </span>
                                <Link
                                    href={appointment.againUrl}
                                    className="inline-flex h-10 shrink-0 items-center rounded-full border border-linha-forte px-4 text-[13px] font-semibold text-cafe-escuro hover:bg-areia"
                                >
                                    Agendar de novo
                                </Link>
                            </li>
                        ))}
                    </ul>
                </section>
            )}

            {salon.whatsappUrl && (
                <p className="text-center text-[13px] text-suave">
                    Dúvidas? Fale com o salão pelo{' '}
                    <a href={salon.whatsappUrl} className="font-semibold text-cafe underline">
                        WhatsApp
                    </a>
                    .
                </p>
            )}
        </BookingLayout>
    );
}
