import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { cx } from '@/lib/format';

type Props = {
    label: string;
    value: ReactNode;
    hint?: ReactNode;
    href?: string;
    highlight?: boolean;
    dark?: boolean;
    className?: string;
};

/** Quadro com um número em destaque (painel, agenda e financeiro) */
export default function Stat({ label, value, hint, href, highlight = false, dark = false, className }: Props) {
    const classes = highlight
        ? 'rounded-cartao bg-cafe text-white shadow-cartao hover:bg-cafe-escuro'
        : dark
          ? 'rounded-cartao bg-tinta text-creme shadow-cartao'
          : cx('cartao text-tinta', href && 'hover:bg-areia');
    const muted = highlight ? 'text-pele' : dark ? 'text-menu-suave' : 'text-suave';
    const content = (
        <>
            <span className={cx('text-[13px]', muted)}>{label}</span>
            <span className="text-2xl font-bold">{value}</span>
            {hint && <span className={cx('text-[13px]', muted)}>{hint}</span>}
        </>
    );
    const className_ = cx('flex flex-col gap-1 px-5 py-4 transition', classes, className);

    return href ? (
        <Link href={href} className={className_}>
            {content}
        </Link>
    ) : (
        <div className={className_}>{content}</div>
    );
}
