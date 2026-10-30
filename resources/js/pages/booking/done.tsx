import { Link } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '@/components/Icon';
import Selo from '@/components/Selo';
import BookingLayout, { type BookingSalon, InfoItem } from '@/layouts/BookingLayout';
import type { Status } from '@/types';

type Props = {
    salon: BookingSalon;
    firstName: string;
    appointment: { date: string; time: string; service: string; details: string; professional: string; status: Status };
    calendarUrl: string;
    mineUrl: string;
};

/** Passo 4: horário reservado */
export default function Done({ salon, firstName, appointment, calendarUrl, mineUrl }: Props) {
    const [copied, setCopied] = useState(false);

    const copyLink = () => {
        // O link precisa ser completo para funcionar em outro celular
        void navigator.clipboard.writeText(new URL(mineUrl, window.location.href).href);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <BookingLayout salon={salon} title="Agendamento feito" close={mineUrl}>
            <div className="flex flex-col items-center gap-3 pt-2 text-center">
                <span className="flex h-20 w-20 items-center justify-center rounded-full bg-pele text-cafe">
                    <Icon name="check" className="h-9 w-9" />
                </span>
                <span className="text-xs font-bold uppercase tracking-[0.18em] text-cafe">Agendamento feito</span>
                <h1 className="text-[32px] font-medium leading-tight">
                    Tudo certo, <em className="italic text-cafe">{firstName}</em>!
                </h1>
                <p className="max-w-[320px] text-[15px] leading-relaxed text-suave">Seu horário está reservado. Se precisar mudar alguma coisa, é só remarcar em "Meus horários".</p>
            </div>

            <section className="overflow-hidden rounded-cartao border border-linha bg-cartao shadow-cartao">
                <div className="grid grid-cols-2 bg-tinta text-creme">
                    <div className="flex flex-col gap-0.5 border-r border-menu-ativo px-4 py-3.5">
                        <span className="text-[11px] font-bold uppercase tracking-[0.14em] text-menu-suave">Data</span>
                        <span className="text-lg font-bold">{appointment.date}</span>
                    </div>
                    <div className="flex flex-col gap-0.5 px-4 py-3.5">
                        <span className="text-[11px] font-bold uppercase tracking-[0.14em] text-menu-suave">Horário</span>
                        <span className="text-lg font-bold">{appointment.time}</span>
                    </div>
                </div>
                <div className="flex flex-col gap-3 p-4">
                    <div className="flex items-start justify-between gap-3">
                        <div className="flex flex-col gap-0.5">
                            <span className="text-base font-bold">{appointment.service}</span>
                            <span className="text-[13px] text-suave">{appointment.details}</span>
                        </div>
                        <Selo color={appointment.status.color}>{appointment.status.label}</Selo>
                    </div>
                    <ul className="flex flex-col gap-2 border-t border-linha-clara pt-3 text-sm">
                        <InfoItem icon="usuario">Com {appointment.professional}</InfoItem>
                        {salon.city && (
                            <InfoItem icon="local">
                                {salon.name} · {salon.city}
                            </InfoItem>
                        )}
                    </ul>
                </div>
            </section>

            <div className="flex flex-col gap-2.5">
                {/* Arquivo .ics: link comum para o celular baixar */}
                <a href={calendarUrl} className="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-cafe text-[15px] font-semibold text-white hover:bg-cafe-escuro">
                    <Icon name="agenda" className="h-[18px] w-[18px]" />
                    Adicionar ao calendário
                </a>
                <Link href={mineUrl} className="inline-flex h-12 items-center justify-center rounded-full border border-linha-forte bg-cartao text-[15px] font-semibold hover:bg-areia">
                    Ver meus horários
                </Link>
            </div>

            <div className="flex flex-col items-center gap-2 text-center text-[13px] leading-relaxed text-suave">
                <p>Este aparelho já guarda os seus horários. Para ver em outro celular, guarde o link de "Meus horários".</p>
                <button type="button" className="inline-flex min-h-11 items-center gap-1.5 font-semibold text-cafe" onClick={copyLink}>
                    <Icon name="link" className="h-4 w-4" />
                    {copied ? 'Link copiado!' : 'Copiar meu link'}
                </button>
                {salon.whatsappUrl && (
                    <p>
                        Dúvidas? Fale com o salão pelo{' '}
                        <a href={salon.whatsappUrl} className="font-semibold text-cafe underline">
                            WhatsApp
                        </a>
                        .
                    </p>
                )}
            </div>
        </BookingLayout>
    );
}
