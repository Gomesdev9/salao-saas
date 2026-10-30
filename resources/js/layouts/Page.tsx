import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import Flash from '@/components/Flash';
import Icon from '@/components/Icon';
import AppLayout from './AppLayout';

type Props = {
    title: string;
    subtitle?: ReactNode;
    eyebrow?: ReactNode;
    back?: string;
    backLabel?: string;
    actions?: ReactNode;
    children?: ReactNode;
};

/** Tela padrão: título, botões à direita, aviso de sucesso e o conteúdo */
export default function Page({ title, subtitle, eyebrow, back, backLabel = 'Voltar', actions, children }: Props) {
    return (
        <AppLayout title={title}>
            <div className="flex flex-col gap-5">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div className="flex flex-col gap-1">
                        {back && (
                            <Link href={back} className="mb-1 inline-flex items-center gap-1.5 text-sm font-semibold text-cafe hover:text-cafe-escuro">
                                <Icon name="chevron-left" className="h-4 w-4" />
                                {backLabel}
                            </Link>
                        )}
                        {eyebrow && <span className="text-[13px] font-semibold uppercase tracking-wider text-cafe">{eyebrow}</span>}
                        <h1 className="text-[28px] font-semibold leading-tight sm:text-[32px]">{title}</h1>
                        {subtitle && <span className="text-sm text-suave">{subtitle}</span>}
                    </div>
                    {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
                </div>

                <Flash />

                {children}
            </div>
        </AppLayout>
    );
}
