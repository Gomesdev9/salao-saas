import { router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { cx } from '@/lib/format';

/** Botão vermelho de texto que pergunta antes de remover (DELETE) */
export default function RemoveButton({ href, confirm, className, children }: { href: string; confirm: string; className?: string; children: ReactNode }) {
    return (
        <button
            type="button"
            onClick={() => window.confirm(confirm) && router.delete(href)}
            className={cx('min-h-11 text-sm font-semibold text-perigo hover:underline', className)}
        >
            {children}
        </button>
    );
}
