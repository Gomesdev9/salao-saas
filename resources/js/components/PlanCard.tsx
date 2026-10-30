import type { ReactNode } from 'react';
import { cx } from '@/lib/format';
import type { PlanInfo } from '@/types';
import Icon from './Icon';

/** Cartão de um plano (página de divulgação e "Meu plano"). O botão vem em children. */
export default function PlanCard({ plan, current = false, children }: { plan: PlanInfo; current?: boolean; children?: ReactNode }) {
    const highlight = plan.highlighted;
    const muted = highlight ? 'text-menu-texto' : 'text-suave';

    return (
        <article
            className={cx(
                'relative flex flex-col gap-5 rounded-cartao p-6 sm:p-7',
                highlight ? 'bg-tinta text-creme shadow-menu' : 'cartao',
                current && 'ring-2 ring-cafe ring-offset-2 ring-offset-creme',
            )}
        >
            {highlight && <span className="absolute -top-3.5 left-6 rounded-full bg-cafe px-3 py-1 text-xs font-bold text-white">Mais escolhido</span>}

            <div className="flex flex-col gap-1.5">
                <div className="flex items-center justify-between gap-3">
                    <h3 className="font-display text-2xl font-semibold">{plan.label}</h3>
                    {current && <span className="selo border-transparent bg-[#E7F4EC] text-[#1C5234]">Seu plano</span>}
                </div>
                <p className={cx('text-sm', muted)}>{plan.description}</p>
            </div>

            <p className="flex items-baseline gap-1">
                <span className="text-lg font-semibold">R$</span>
                <span className="text-[44px] font-bold leading-none tracking-tight">{plan.formattedPrice.replace('R$ ', '')}</span>
                <span className={cx('text-sm', muted)}>/mês</span>
            </p>

            <ul className="flex flex-col gap-2.5 text-[15px]">
                {plan.features.map((feature) => (
                    <li key={feature.text} className="flex items-start gap-2.5">
                        <Icon name="check" className={cx('mt-0.5 h-[18px] w-[18px]', highlight ? 'text-[#D9A57F]' : 'text-cafe')} />
                        <span>
                            {feature.text}
                            {feature.soon && (
                                <span
                                    className={cx(
                                        'ml-1 inline-block rounded-full border border-dashed px-2 py-px text-[11px] font-semibold uppercase tracking-wide',
                                        highlight ? 'border-menu-suave text-menu-suave' : 'border-linha-forte text-suave',
                                    )}
                                >
                                    Em breve
                                </span>
                            )}
                        </span>
                    </li>
                ))}
            </ul>

            {children && <div className="mt-auto pt-1">{children}</div>}
        </article>
    );
}
