import { useForm } from '@inertiajs/react';
import { InputError, InputLabel, TextareaInput, TextInput } from '@/components/Form';
import BookingLayout, { BarButton, type BookingSalon, InfoItem } from '@/layouts/BookingLayout';
import type { BookingService } from './service';

type Props = {
    salon: BookingSalon;
    accountUrl: string | null;
    service: BookingService;
    params: Record<string, string | number>;
    storeUrl: string;
    backUrl: string;
    dateLabel: string;
    time: string;
    professionalName: string;
    prefill: { name: string; whatsapp: string };
    replacesOld: boolean;
};

/** Passo 3: nome e WhatsApp da cliente */
export default function Details({ salon, service, params, backUrl, prefill, ...props }: Props) {
    const form = useForm({ ...params, name: prefill.name, whatsapp: prefill.whatsapp, notes: '' });

    return (
        <BookingLayout
            salon={salon}
            title="Seus dados"
            step={3}
            back={backUrl}
            account={props.accountUrl}
            heading={
                <>
                    Quase lá! Confira seus <em>dados</em>
                </>
            }
            bar={
                <>
                    <span className="flex min-w-0 flex-col">
                        <span className="text-xs text-menu-suave">Total</span>
                        <span className="text-base font-bold">{service.price}</span>
                    </span>
                    <BarButton form="dados" disabled={form.processing}>
                        Confirmar agendamento
                    </BarButton>
                </>
            }
        >
            <section className="cartao flex flex-col gap-3 p-4">
                <div className="flex items-start justify-between gap-3">
                    <div className="flex min-w-0 flex-col gap-0.5">
                        <span className="text-base font-bold">{service.name}</span>
                        <span className="text-[13px] text-suave">{service.details}</span>
                    </div>
                    <a href={backUrl} className="inline-flex min-h-11 items-center text-sm font-semibold text-cafe underline">
                        Alterar
                    </a>
                </div>
                <ul className="flex flex-col gap-2 border-t border-linha-clara pt-3 text-sm">
                    <InfoItem icon="agenda">
                        <strong>{props.dateLabel}</strong> às {props.time}
                    </InfoItem>
                    <InfoItem icon="usuario">Com {props.professionalName}</InfoItem>
                    {salon.city && (
                        <InfoItem icon="local">
                            {salon.name} · {salon.city}
                        </InfoItem>
                    )}
                </ul>
            </section>

            <form
                id="dados"
                className="flex flex-col gap-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(props.storeUrl);
                }}
            >
                <div>
                    <InputLabel htmlFor="name" value="Nome completo" />
                    <TextInput id="name" type="text" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required autoComplete="name" className="h-12" />
                    <InputError message={form.errors.name} />
                </div>
                <div>
                    <InputLabel htmlFor="whatsapp" value="WhatsApp" />
                    <TextInput
                        id="whatsapp"
                        type="tel"
                        inputMode="tel"
                        value={form.data.whatsapp}
                        onChange={(e) => form.setData('whatsapp', e.target.value)}
                        required
                        autoComplete="tel"
                        placeholder="(11) 98765-4321"
                        className="h-12"
                    />
                    <InputError message={form.errors.whatsapp} />
                    <p className="mt-1.5 text-xs text-suave">O salão usa o seu WhatsApp só para falar sobre os seus horários.</p>
                </div>
                <div>
                    <InputLabel htmlFor="notes">
                        Algo que a gente precisa saber? <span className="font-normal text-suave">(opcional)</span>
                    </InputLabel>
                    <TextareaInput
                        id="notes"
                        rows={3}
                        value={form.data.notes}
                        onChange={(e) => form.setData('notes', e.target.value)}
                        placeholder="Alergias, gestação, procedimentos recentes..."
                    />
                    <InputError message={form.errors.notes} />
                </div>

                {props.replacesOld && <p className="rounded-campo bg-mel px-4 py-3 text-sm text-mel-texto">Ao confirmar, o horário antigo é cancelado e fica valendo este.</p>}
            </form>
        </BookingLayout>
    );
}
