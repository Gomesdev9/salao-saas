import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { cx } from '@/lib/format';

/** Botão arredondado de filtro (Ativos/Inativos, categorias). O marcado fica escuro. */
export default function Chip({ href, on, children }: { href: string; on: boolean; children: ReactNode }) {
    return (
        <Link
            href={href}
            aria-current={on ? 'page' : undefined}
            className={cx('inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold', on ? 'border-tinta bg-tinta text-white' : 'border-linha bg-cartao hover:bg-areia')}
        >
            {children}
        </Link>
    );
}
