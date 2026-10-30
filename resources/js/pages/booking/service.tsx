import { router } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '@/components/Icon';
import { cx } from '@/lib/format';
import BookingLayout, { BarButton, BarSummary, type BookingSalon, WhatsappButton } from '@/layouts/BookingLayout';

export type BookingService = { id: number; name: string; details: string; price: string };

type Props = {
    salon: BookingSalon;
    accountUrl: string | null;
    groups: { name: string; services: BookingService[] }[];
    selectedId: number | null;
    nextUrl: string;
    reschedule: number | null;
};

/** Passo 1: o procedimento */
export default function Service({ salon, accountUrl, groups, selectedId, nextUrl, reschedule }: Props) {
    const services = groups.flatMap((group) => group.services);
    const [selected, setSelected] = useState(selectedId);
    const [tab, setTab] = useState(groups.find((group) => group.services.some((service) => service.id === selectedId))?.name ?? groups[0]?.name);
    const chosen = services.find((service) => service.id === selected);

    return (
        <BookingLayout
            salon={salon}
            title="Agendar"
            step={1}
            account={accountUrl}
            heading={
                <>
                    Qual procedimento você quer <em>agendar</em>?
                </>
            }
            bar={
                services.length > 0 && (
                    <>
                        <BarSummary label="Procedimento" text={chosen?.name ?? 'Escolha na lista'} />
                        <BarButton form="escolha">
                            Continuar
                            <Icon name="seta" className="h-[18px] w-[18px]" />
                        </BarButton>
                    </>
                )
            }
        >
            {services.length === 0 ? (
                <div className="cartao flex flex-col gap-3 p-5 text-[15px] leading-relaxed text-suave">
                    Ainda não há procedimentos para agendar por aqui.
                    {salon.whatsappUrl && <WhatsappButton href={salon.whatsappUrl}>Falar com o salão no WhatsApp</WhatsappButton>}
                </div>
            ) : (
                <>
                    <form
                        id="escolha"
                        className="flex flex-col gap-4"
                        onSubmit={(event) => {
                            event.preventDefault();
                            router.get(nextUrl, { servico: selected, ...(reschedule ? { remarcar: reschedule } : {}) });
                        }}
                    >
                        {groups.length > 1 && (
                            <div className="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1" role="tablist" aria-label="Categorias">
                                {groups.map((group) => (
                                    <button
                                        key={group.name}
                                        type="button"
                                        role="tab"
                                        aria-selected={tab === group.name}
                                        onClick={() => setTab(group.name)}
                                        className={cx(
                                            'flex min-h-11 shrink-0 items-center rounded-full border px-5 text-sm font-semibold',
                                            tab === group.name ? 'border-tinta bg-tinta text-white' : 'border-linha bg-cartao',
                                        )}
                                    >
                                        {group.name}
                                    </button>
                                ))}
                            </div>
                        )}

                        {groups.map((group) => (
                            <fieldset key={group.name} className={cx('flex flex-col gap-3', tab !== group.name && 'hidden')}>
                                <legend className="sr-only">{group.name}</legend>
                                {group.services.map((service) => (
                                    <label
                                        key={service.id}
                                        className="flex cursor-pointer items-center gap-3 rounded-[18px] border border-linha bg-cartao px-4 py-3.5 shadow-cartao transition has-[:checked]:border-cafe has-[:checked]:ring-1 has-[:checked]:ring-cafe has-[:focus-visible]:ring-2"
                                    >
                                        <input
                                            type="radio"
                                            name="servico"
                                            value={service.id}
                                            required
                                            className="peer sr-only"
                                            checked={selected === service.id}
                                            onChange={() => setSelected(service.id)}
                                        />
                                        <span className="flex min-w-0 flex-1 flex-col gap-0.5">
                                            <span className="text-base font-bold">{service.name}</span>
                                            <span className="text-[13px] text-suave">{service.details}</span>
                                        </span>
                                        <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-linha-forte text-transparent peer-checked:border-cafe peer-checked:bg-cafe peer-checked:text-white">
                                            <Icon name="check" className="h-4 w-4" />
                                        </span>
                                    </label>
                                ))}
                            </fieldset>
                        ))}
                    </form>

                    {salon.whatsappUrl && (
                        <a href={salon.whatsappUrl} className="self-center text-sm font-semibold text-cafe underline">
                            Não sabe qual escolher? Fale com a gente
                        </a>
                    )}
                </>
            )}
        </BookingLayout>
    );
}
