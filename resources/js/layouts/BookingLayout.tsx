import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import Icon from '@/components/Icon';
import { cx } from '@/lib/format';

export type BookingSalon = { name: string; city: string | null; whatsappUrl: string | null };

const circle = 'flex h-11 w-11 items-center justify-center rounded-full border border-linha bg-cartao shadow-cartao hover:bg-areia';

/** Página pública de agendamento da cliente: pensada para o celular, centralizada no computador */
export default function BookingLayout({
    salon,
    title,
    step,
    back,
    account,
    close,
    heading,
    bar,
    children,
}: {
    salon: BookingSalon;
    title: string;
    step?: number;
    back?: string;
    account?: string | null;
    close?: string;
    heading?: ReactNode;
    bar?: ReactNode;
    children: ReactNode;
}) {
    return (
        <>
            <Head title={`${title} · ${salon.name}`} />
            <div className="mx-auto flex min-h-screen w-full max-w-[480px] flex-col gap-5 px-4 pb-36 pt-4">
                <header className="grid grid-cols-[44px_1fr_44px] items-center gap-2">
                    {back ? (
                        <Link href={back} className={circle}>
                            <span className="sr-only">Voltar</span>
                            <Icon name="chevron-left" className="h-5 w-5" />
                        </Link>
                    ) : (
                        <span />
                    )}

                    <span className="truncate text-center font-display text-xl italic">{salon.name}</span>

                    {close ? (
                        <Link href={close} className={circle}>
                            <span className="sr-only">Fechar</span>
                            <Icon name="fechar" className="h-5 w-5" />
                        </Link>
                    ) : account ? (
                        <Link href={account} className={circle}>
                            <span className="sr-only">Meus horários</span>
                            <Icon name="usuario" className="h-5 w-5" />
                        </Link>
                    ) : (
                        <span />
                    )}
                </header>

                {heading && (
                    <div className="flex flex-col gap-2">
                        {step && <span className="text-xs font-bold uppercase tracking-[0.18em] text-cafe">Passo {step} de 4</span>}
                        <h1 className="text-[30px] font-medium leading-[1.12] [&_em]:italic [&_em]:text-cafe">{heading}</h1>
                        {step && (
                            <div className="mt-2 grid grid-cols-4 gap-1.5" aria-hidden="true">
                                {[1, 2, 3, 4].map((i) => (
                                    <span key={i} className={cx('h-[3px] rounded-full', i <= step ? 'bg-cafe' : 'bg-linha-forte')} />
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {children}
            </div>

            {bar && (
                // Barra fixa no rodapé com o resumo e o botão de seguir
                <div className="fixed inset-x-0 bottom-0 z-30 px-4 pb-4">
                    <div className="mx-auto flex max-w-[448px] items-center justify-between gap-3 rounded-full bg-tinta py-2.5 pl-6 pr-2.5 text-creme shadow-menu">{bar}</div>
                </div>
            )}
        </>
    );
}

/** Botão claro "Continuar" da barra do rodapé */
export function BarButton({ form, disabled, children }: { form: string; disabled?: boolean; children: ReactNode }) {
    return (
        <button
            type="submit"
            form={form}
            disabled={disabled}
            className="inline-flex h-12 shrink-0 items-center gap-2 rounded-full bg-pele px-5 text-[15px] font-semibold text-tinta hover:bg-white disabled:opacity-60"
        >
            {children}
        </button>
    );
}

/** Linha de resumo da barra do rodapé: um rótulo pequeno e o texto em destaque */
export function BarSummary({ label, text }: { label: string; text: string }) {
    return (
        <span className="flex min-w-0 flex-col">
            <span className="truncate text-xs text-menu-suave">{label}</span>
            <span className="truncate text-sm font-bold">{text}</span>
        </span>
    );
}

/** Lista com ícone: data, profissional e endereço do horário */
export function InfoItem({ icon, children }: { icon: string; children: ReactNode }) {
    return (
        <li className="flex items-center gap-2.5">
            <Icon name={icon} className="h-[18px] w-[18px] text-cafe" />
            <span>{children}</span>
        </li>
    );
}

export function WhatsappButton({ href, children }: { href: string; children: ReactNode }) {
    return (
        <a href={href} className="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-cafe font-semibold text-white hover:bg-cafe-escuro">
            <Icon name="whatsapp" className="h-5 w-5" />
            {children}
        </a>
    );
}
