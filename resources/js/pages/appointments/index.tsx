import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Avatar from '@/components/Avatar';
import { LinkButton } from '@/components/Buttons';
import Flash from '@/components/Flash';
import Icon from '@/components/Icon';
import Selo from '@/components/Selo';
import Stat from '@/components/Stat';
import AppLayout from '@/layouts/AppLayout';
import { cx, money, plural, shortMoney } from '@/lib/format';
import type { Status } from '@/types';

type Appointment = {
    id: number;
    professionalId: number;
    time: string;
    client: string;
    service: string;
    professional: string;
    price: number;
    status: Status;
    canceled: boolean;
    upcoming: boolean;
    offset: number;
    minutes: number;
};

type Professional = { id: number; name: string; firstName: string; area: string | null; count: number };

type Props = {
    day: { date: string; isToday: boolean; short: string; long: string; previous: string; next: string };
    week: { date: string; weekday: string; day: number }[];
    appointments: Appointment[];
    filterId: number | null;
    professionals: Professional[];
    statuses: Status[];
    startHour: number;
    endHour: number;
    now: { time: string; offset: number | null };
    total: number;
    count: number;
    doneCount: number;
    missedCount: number;
};

// Cada hora ocupa 80px de altura na grade (30 min = 40px)
const HOUR_PX = 80;
const PX_PER_MINUTE = HOUR_PX / 60;

const pad = (value: number) => String(value).padStart(2, '0');

/** Escolher outra data no calendário recarrega a agenda daquele dia */
const goToDay = (date: string, professional?: number | null) => date && router.get(route('appointments.index'), { data: date, ...(professional ? { profissional: professional } : {}) });

/** Agenda do dia: uma coluna por profissional no computador e uma lista (com filtro por profissional) no celular */
export default function AppointmentsIndex(props: Props) {
    return (
        <AppLayout title="Agenda" wide>
            <PhoneAgenda {...props} />
            <DesktopAgenda {...props} />
        </AppLayout>
    );
}

function NoProfessionals() {
    return (
        <div className="cartao p-6 text-suave">
            Cadastre pelo menos um{' '}
            <Link href={route('professionals.create')} className="font-semibold text-cafe underline">
                profissional
            </Link>{' '}
            para usar a agenda.
        </div>
    );
}

function NowLine({ time }: { time: string }) {
    return (
        <div className="flex items-center gap-2 text-xs font-bold text-cafe">
            <span>Agora · {time}</span>
            <span className="h-0.5 flex-1 bg-cafe" />
        </div>
    );
}

/** Celular e tablet */
function PhoneAgenda({ day, week, appointments, filterId, professionals, now }: Props) {
    const { salon } = usePage().props.auth!;
    const [showPast, setShowPast] = useState(false);
    const list = filterId ? appointments.filter((appointment) => appointment.professionalId === filterId) : appointments;
    // Hoje, a lista começa no "Agora": os horários que já passaram ficam atrás de um botão
    const nowIndex = day.isToday ? list.findIndex((appointment) => appointment.upcoming) : -1;
    const chip = 'flex min-h-10 shrink-0 items-center rounded-full border px-3.5 text-[13px] font-semibold';
    const withFilter = (params: Record<string, string | number>) => ({ ...params, ...(filterId ? { profissional: filterId } : {}) });

    return (
        <div className="flex flex-col gap-3 lg:hidden">
            <header className="-mx-4 -mt-5 flex flex-col gap-3.5 bg-tinta px-4 pb-3.5 pt-5 text-creme sm:-mx-6 sm:px-6">
                <div className="flex items-center justify-between gap-3">
                    <div className="flex min-w-0 flex-col">
                        <span className="truncate text-xs text-menu-suave">{salon.name}</span>
                        <h1 className="text-[22px] font-bold leading-tight">{day.short}</h1>
                    </div>
                    <label className="relative flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded-2xl border border-menu-ativo">
                        <span className="sr-only">Escolher data</span>
                        <Icon name="agenda" />
                        <input type="date" value={day.date} onChange={(e) => goToDay(e.target.value, filterId)} className="absolute inset-0 h-full w-full cursor-pointer opacity-0" />
                    </label>
                </div>
                <nav aria-label="Dias da semana" className="grid grid-cols-7 gap-1">
                    {week.map((weekDay) => {
                        const selected = weekDay.date === day.date;

                        return (
                            <Link
                                key={weekDay.date}
                                href={route('appointments.index', withFilter({ data: weekDay.date }))}
                                aria-current={selected ? 'date' : undefined}
                                className={cx('flex h-14 flex-col items-center justify-center gap-0.5 rounded-2xl', selected ? 'bg-cafe text-white' : 'text-menu-texto')}
                            >
                                <span className="text-[11px]">{weekDay.weekday}</span>
                                <span className="text-base font-bold">{weekDay.day}</span>
                            </Link>
                        );
                    })}
                </nav>
            </header>

            <Flash />

            {professionals.length === 0 ? (
                <NoProfessionals />
            ) : (
                <>
                    <div className="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:-mx-6 sm:px-6" aria-label="Filtrar por profissional">
                        <Link
                            href={route('appointments.index', { data: day.date })}
                            aria-current={!filterId ? 'true' : undefined}
                            className={cx(chip, filterId ? 'border-linha bg-cartao' : 'border-tinta bg-tinta text-white')}
                        >
                            Todos · {appointments.length}
                        </Link>
                        {professionals.map((professional) => (
                            <Link
                                key={professional.id}
                                href={route('appointments.index', { data: day.date, profissional: professional.id })}
                                aria-current={filterId === professional.id ? 'true' : undefined}
                                className={cx(chip, filterId === professional.id ? 'border-tinta bg-tinta text-white' : 'border-linha bg-cartao')}
                            >
                                {professional.firstName} · {professional.count}
                            </Link>
                        ))}
                    </div>

                    <div className="flex flex-col gap-2">
                        {nowIndex > 0 && !showPast && (
                            <button
                                type="button"
                                onClick={() => setShowPast(true)}
                                className="flex min-h-11 items-center justify-center rounded-full border border-dashed border-linha-forte text-[13px] font-semibold text-suave"
                            >
                                Ver {plural(nowIndex, 'horário anterior', 'horários anteriores')}
                            </button>
                        )}
                        {list.length > 0 ? (
                            list.map((appointment, i) => (
                                <div key={appointment.id} className="contents">
                                    {nowIndex === i && <NowLine time={now.time} />}
                                    {(nowIndex <= 0 || i >= nowIndex || showPast) && (
                                        <div className="flex items-stretch gap-3">
                                            <span className="w-11 shrink-0 pt-3 text-sm font-bold">{appointment.time}</span>
                                            <Link
                                                href={route('appointments.edit', appointment.id)}
                                                className="flex min-w-0 flex-1 flex-col gap-1 rounded-[18px] border border-linha bg-cartao px-3.5 py-2.5 shadow-cartao"
                                            >
                                                <span className="flex items-center justify-between gap-2">
                                                    <span className={cx('truncate text-[15px] font-bold', appointment.canceled && 'text-suave line-through')}>{appointment.client}</span>
                                                    <Selo color={appointment.status.color} className="shrink-0 text-[11px]">
                                                        {appointment.status.label}
                                                    </Selo>
                                                </span>
                                                <span className="truncate text-[13px] text-suave">
                                                    {appointment.service} · {appointment.professional} · {shortMoney(appointment.price)}
                                                </span>
                                            </Link>
                                        </div>
                                    )}
                                </div>
                            ))
                        ) : (
                            <div className="cartao flex flex-col items-center gap-1 p-6 text-center text-sm text-suave">
                                Nenhum horário neste dia.
                                <Link href={route('appointments.create', withFilter({ data: day.date }))} className="inline-flex min-h-11 items-center font-semibold text-cafe">
                                    Agendar um horário
                                </Link>
                            </div>
                        )}
                        {day.isToday && nowIndex === -1 && list.length > 0 && <NowLine time={now.time} />}
                    </div>
                </>
            )}

            <Link
                href={route('appointments.create', withFilter({ data: day.date }))}
                className="fixed bottom-[98px] right-4 z-30 flex h-14 w-14 items-center justify-center rounded-[22px] bg-cafe text-white shadow-[0_6px_16px_rgba(28,13,6,0.25)] hover:bg-cafe-escuro"
            >
                <span className="sr-only">Novo agendamento</span>
                <Icon name="mais" className="h-6 w-6" />
            </Link>
        </div>
    );
}

/** Computador */
function DesktopAgenda({ day, appointments, professionals, statuses, startHour, endHour, now, total, count, doneCount, missedCount }: Props) {
    const hours = Array.from({ length: endHour - startHour }, (_, i) => startHour + i);
    const gridHeight = hours.length * HOUR_PX;
    // Links para agendar em horário vazio, a cada 30 min
    const slots = Array.from({ length: hours.length * 2 }, (_, i) => i * 30);
    const slotTime = (minutes: number) => `${pad(startHour + Math.floor(minutes / 60))}:${pad(minutes % 60)}`;

    return (
        <div className="hidden flex-col gap-5 lg:flex">
            <div className="flex flex-wrap items-end justify-between gap-4">
                <div className="flex flex-col gap-1">
                    <span className="text-[13px] font-semibold uppercase tracking-wider text-cafe">Agenda</span>
                    <h1 className="text-[28px] font-semibold leading-tight sm:text-[32px]">{day.long}</h1>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    <div className="flex h-11 items-center rounded-campo border border-linha bg-cartao">
                        <Link href={route('appointments.index', { data: day.previous })} className="flex h-11 w-11 items-center justify-center hover:text-cafe">
                            <span className="sr-only">Dia anterior</span>
                            <Icon name="chevron-left" className="h-[18px] w-[18px]" />
                        </Link>
                        <Link href={route('appointments.index')} className="flex h-11 items-center border-x border-linha px-3.5 text-sm font-semibold hover:text-cafe">
                            Hoje
                        </Link>
                        <Link href={route('appointments.index', { data: day.next })} className="flex h-11 w-11 items-center justify-center hover:text-cafe">
                            <span className="sr-only">Próximo dia</span>
                            <Icon name="chevron-right" className="h-[18px] w-[18px]" />
                        </Link>
                    </div>
                    <label htmlFor="data" className="sr-only">
                        Escolher data
                    </label>
                    <input id="data" type="date" value={day.date} onChange={(e) => goToDay(e.target.value)} className="campo w-auto text-sm" />
                    <LinkButton href={route('appointments.create', { data: day.date })} icon="mais">
                        Novo agendamento
                    </LinkButton>
                </div>
            </div>

            <Flash />

            <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Stat label="Previsto no dia" value={money(total)} />
                <Stat label="Atendimentos" value={count} />
                <Stat label="Já atendidos" value={doneCount} />
                <Stat label="Faltas e cancelamentos" value={missedCount} />
            </div>

            {professionals.length === 0 ? (
                <NoProfessionals />
            ) : (
                <>
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="flex flex-wrap gap-2" aria-label="Legenda das situações">
                            {statuses.map((status) => (
                                <Selo key={status.value} color={status.color}>
                                    {status.label}
                                </Selo>
                            ))}
                        </div>
                        <span className="text-[13px] text-suave">Clique num horário vazio para agendar.</span>
                    </div>

                    <div className="cartao overflow-x-auto">
                        <div className="flex min-w-max">
                            {/* Coluna das horas */}
                            <div className="w-16 shrink-0 border-r border-linha">
                                <div className="h-16 border-b border-linha" />
                                <div className="relative" style={{ height: gridHeight }}>
                                    {hours.map((hour) => (
                                        <span key={hour} className="absolute right-2.5 text-xs text-suave" style={{ top: (hour - startHour) * HOUR_PX + 4 }}>
                                            {pad(hour)}:00
                                        </span>
                                    ))}
                                </div>
                            </div>

                            {/* Uma coluna por profissional */}
                            <div className="relative flex flex-1">
                                {professionals.map((professional) => (
                                    <div key={professional.id} className="w-[200px] flex-1 border-r border-linha last:border-r-0">
                                        <div className="flex h-16 items-center gap-2.5 border-b border-linha px-3.5">
                                            <Avatar name={professional.name} />
                                            <div className="flex min-w-0 flex-col">
                                                <span className="truncate text-sm font-semibold">{professional.name}</span>
                                                <span className="truncate text-xs text-suave">
                                                    {professional.area ? `${professional.area} · ` : ''}
                                                    {plural(professional.count, 'horário', 'horários')}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="relative" style={{ height: gridHeight }}>
                                            {slots.map((minutes) => (
                                                <Link
                                                    key={minutes}
                                                    href={route('appointments.create', { data: day.date, hora: slotTime(minutes), profissional: professional.id })}
                                                    className={cx('group absolute inset-x-0 block border-t', minutes % 60 === 0 ? 'border-linha' : 'border-linha-clara')}
                                                    style={{ top: minutes * PX_PER_MINUTE, height: 40 }}
                                                    title={`Agendar às ${slotTime(minutes)}`}
                                                >
                                                    <span className="absolute inset-x-1.5 inset-y-1 hidden items-center gap-1.5 rounded-[10px] border border-dashed border-caramelo bg-[#FDF3EC] px-2 text-xs font-semibold text-cafe group-hover:flex">
                                                        <Icon name="mais" className="h-3.5 w-3.5" />
                                                        Agendar às {slotTime(minutes)}
                                                    </span>
                                                </Link>
                                            ))}

                                            {appointments
                                                .filter((appointment) => appointment.professionalId === professional.id)
                                                .map((appointment) => (
                                                    <Link
                                                        key={appointment.id}
                                                        href={route('appointments.edit', appointment.id)}
                                                        className={cx(
                                                            'absolute inset-x-1.5 overflow-hidden rounded-[10px] border px-2 py-1 text-xs leading-[15px] shadow-sm transition hover:shadow-md',
                                                            appointment.status.color,
                                                            appointment.canceled && 'line-through',
                                                        )}
                                                        style={{ top: appointment.offset * PX_PER_MINUTE, height: Math.max(28, appointment.minutes * PX_PER_MINUTE - 4) }}
                                                    >
                                                        <span className="block truncate font-bold">
                                                            {appointment.time} {appointment.client}
                                                        </span>
                                                        <span className="block truncate">
                                                            {appointment.service} · {appointment.status.label}
                                                        </span>
                                                    </Link>
                                                ))}
                                        </div>
                                    </div>
                                ))}

                                {/* Linha da hora atual */}
                                {now.offset !== null && (
                                    <>
                                        <div aria-hidden="true" className="pointer-events-none absolute inset-x-0 h-0.5 bg-cafe" style={{ top: 64 + now.offset * PX_PER_MINUTE }} />
                                        <span
                                            className="pointer-events-none absolute left-1.5 rounded-lg bg-cafe px-1.5 py-px text-[11px] font-bold text-white"
                                            style={{ top: 64 + now.offset * PX_PER_MINUTE - 9 }}
                                        >
                                            {now.time}
                                        </span>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
