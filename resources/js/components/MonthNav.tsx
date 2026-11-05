import { Link } from '@inertiajs/react';
import Icon from './Icon';

export type Month = { value: string; label: string; previous: string; next: string };

/** Troca de mês: ‹ Out 2026 › */
export default function MonthNav({ month, routeName }: { month: Month; routeName: string }) {
    return (
        <div className="flex items-center rounded-campo border border-linha bg-cartao">
            <Link href={route(routeName, { mes: month.previous })} className="flex h-11 w-11 items-center justify-center rounded-l-campo hover:bg-areia">
                <span className="sr-only">Mês anterior</span>
                <Icon name="chevron-left" className="h-[18px] w-[18px]" />
            </Link>
            <span className="px-2.5 text-sm font-semibold">{month.label}</span>
            <Link href={route(routeName, { mes: month.next })} className="flex h-11 w-11 items-center justify-center rounded-r-campo hover:bg-areia">
                <span className="sr-only">Próximo mês</span>
                <Icon name="chevron-right" className="h-[18px] w-[18px]" />
            </Link>
        </div>
    );
}
