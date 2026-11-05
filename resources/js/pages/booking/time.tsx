import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Avatar from '@/components/Avatar';
import Icon from '@/components/Icon';
import { cx } from '@/lib/format';
import BookingLayout, { BarButton, BarSummary, type BookingSalon } from '@/layouts/BookingLayout';
import type { BookingService } from './service';

type Props = {
    salon: BookingSalon;
    accountUrl: string | null;
    service: BookingService;
    changeUrl: string;
    anyProfessionalUrl: string;
    professional: { id: number; name: string; firstName: string } | null;
    professionals: { id: number; name: string; firstName: string; url: string }[];
    month: string;
    days: { date: string; weekday: string; number: number; open: boolean; url: string }[];
    day: string;
    dayLabel: string;
    isOpen: boolean;
    hoursSummary: string;
    periods: { name: string; slots: { time: string; free: boolean }[] }[];
    chosenTime: string | null;
    nextUrl: string;
    params: Record<string, string | number>;
};

const chip = 'flex min-h-11 shrink-0 items-center gap-2 rounded-full border pl-1.5 pr-4 text-sm font-semibold';
const cell = 'flex h-[60px] w-[50px] shrink-0 flex-col items-center justify-center rounded-[16px] border';

/** Passo 2: com quem, o dia e o horário */
export default function Time(props: Props) {
    const { salon, service, professional, days, day, periods } = props;
    const error = usePage().props.errors.hora;
    const [time, setTime] = useState(props.chosenTime);
    const hasFree = periods.some((period) => period.slots.some((slot) => slot.free));

    return (
        <BookingLayout
            salon={salon}
            title="Dia e horário"
            step={2}
            back={props.changeUrl}
            account={props.accountUrl}
            heading={
                <>
                    Escolha o dia e o <em>horário</em>
                </>
            }
            bar={
                hasFree && (
                    <>
                        <BarSummary label={professional?.name ?? 'Qualquer profissional'} text={time ? `${props.dayLabel} · ${time}` : props.dayLabel} />
                        <BarButton form="horario">
                            Continuar
                            <Icon name="seta" className="h-[18px] w-[18px]" />
                        </BarButton>
                    </>
                )
            }
        >
            <div className="flex items-center justify-between gap-3 rounded-[18px] bg-pele px-4 py-3.5">
                <div className="flex min-w-0 flex-col gap-0.5">
                    <span className="truncate text-base font-bold">{service.name}</span>
                    <span className="text-[13px] text-suave">{service.details}</span>
                </div>
                <Link href={props.changeUrl} className="inline-flex h-10 shrink-0 items-center rounded-full bg-cartao px-4 text-sm font-semibold text-cafe-escuro shadow-cartao">
                    Trocar
                </Link>
            </div>

            <section className="flex flex-col gap-2.5">
                <h2 className="font-sans text-[15px] font-semibold">Com quem?</h2>
                <div className="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1">
                    <Link
                        href={props.anyProfessionalUrl}
                        aria-current={professional ? undefined : 'true'}
                        className={cx(chip, professional ? 'border-linha bg-cartao' : 'border-tinta bg-tinta text-white')}
                    >
                        <span className={cx('flex h-8 w-8 items-center justify-center rounded-full', professional ? 'bg-pele text-cafe-escuro' : 'bg-menu-ativo text-[#D9A57F]')}>
                            <Icon name="estrela" className="h-4 w-4" />
                        </span>
                        Sem preferência
                    </Link>
                    {props.professionals.map((item) => {
                        const on = professional?.id === item.id;

                        return (
                            <Link key={item.id} href={item.url} aria-current={on ? 'true' : undefined} className={cx(chip, on ? 'border-tinta bg-tinta text-white' : 'border-linha bg-cartao')}>
                                <Avatar name={item.name} size="h-8 w-8 text-xs" className={cx(on && '!bg-menu-ativo !text-creme')} />
                                {item.firstName}
                            </Link>
                        );
                    })}
                </div>
            </section>

            <section className="flex flex-col gap-2.5">
                <div className="flex items-baseline justify-between gap-3">
                    <h2 className="font-sans text-[15px] font-semibold">Dia</h2>
                    <span className="text-[13px] text-suave">{props.month}</span>
                </div>
                <div className="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1">
                    {days.map((option) => {
                        const selected = option.date === day;

                        return option.open ? (
                            <Link
                                key={option.date}
                                href={option.url}
                                aria-current={selected ? 'date' : undefined}
                                className={cx(cell, selected ? 'border-cafe bg-cafe text-white' : 'border-linha bg-cartao hover:border-caramelo')}
                            >
                                <span className={cx('text-[11px] font-medium', selected ? 'text-pele' : 'text-suave')}>{option.weekday}</span>
                                <span className="text-lg font-bold">{option.number}</span>
                            </Link>
                        ) : (
                            <span key={option.date} className={cx(cell, 'border-transparent bg-areia text-suave/60')} title="Fechado">
                                <span className="text-[11px] font-medium">{option.weekday}</span>
                                <span className="text-lg font-bold">{option.number}</span>
                            </span>
                        );
                    })}
                </div>
                <span className="text-xs text-suave">{props.hoursSummary}</span>
            </section>

            <form
                id="horario"
                className="flex flex-col gap-3"
                onSubmit={(event) => {
                    event.preventDefault();
                    router.get(props.nextUrl, { ...props.params, hora: time });
                }}
            >
                <h2 className="font-sans text-[15px] font-semibold">Horários livres</h2>

                {error && (
                    <div role="alert" className="rounded-campo bg-mel px-4 py-3 text-sm font-medium text-mel-texto">
                        {error}
                    </div>
                )}

                {!props.isOpen ? (
                    <p className="cartao p-4 text-sm text-suave">O salão não abre neste dia. Escolha outro dia acima.</p>
                ) : (
                    !hasFree && (
                        <p className="cartao p-4 text-sm text-suave">
                            Não há horário livre neste dia{professional ? ` com ${professional.firstName}` : ''}. Escolha outro dia{professional ? ' ou "Sem preferência"' : ''}.
                        </p>
                    )
                )}

                {periods.map((period) => (
                    <fieldset key={period.name} className="flex flex-col gap-2">
                        <legend className="pb-2 text-xs font-bold uppercase tracking-[0.14em] text-suave">{period.name}</legend>
                        <div className="grid grid-cols-4 gap-2">
                            {period.slots.map((slot) =>
                                slot.free ? (
                                    <label
                                        key={slot.time}
                                        className="flex h-12 cursor-pointer items-center justify-center rounded-campo border border-linha bg-cartao text-[15px] font-semibold transition hover:border-caramelo has-[:checked]:border-cafe has-[:checked]:bg-cafe has-[:checked]:text-white has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-caramelo"
                                    >
                                        <input type="radio" name="hora" value={slot.time} required className="sr-only" checked={time === slot.time} onChange={() => setTime(slot.time)} />
                                        {slot.time}
                                    </label>
                                ) : (
                                    <span key={slot.time} className="flex h-12 items-center justify-center rounded-campo bg-areia text-[15px] text-suave/70 line-through" title="Ocupado">
                                        <span className="sr-only">Ocupado: </span>
                                        {slot.time}
                                    </span>
                                ),
                            )}
                        </div>
                    </fieldset>
                ))}
            </form>
        </BookingLayout>
    );
}
