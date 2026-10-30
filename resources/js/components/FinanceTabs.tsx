import { Link } from '@inertiajs/react';
import { cx } from '@/lib/format';
import type { Month } from './MonthNav';

const tabs = [
    { route: 'finance.index', label: 'Visão geral' },
    { route: 'finance.entries', label: 'Lançamentos' },
    { route: 'finance.commissions', label: 'Comissões' },
];

/** Abas do financeiro, mantendo o mês escolhido */
export default function FinanceTabs({ month }: { month: Month }) {
    return (
        <nav aria-label="Seções do financeiro" className="flex gap-1 overflow-x-auto border-b border-linha">
            {tabs.map((tab) => {
                const active = route().current(tab.route);

                return (
                    <Link
                        key={tab.route}
                        href={route(tab.route, { mes: month.value })}
                        aria-current={active ? 'page' : undefined}
                        className={cx(
                            '-mb-px inline-flex min-h-11 shrink-0 items-center border-b-2 px-3.5 text-sm',
                            active ? 'border-cafe font-bold text-tinta' : 'border-transparent font-semibold text-suave hover:text-tinta',
                        )}
                    >
                        {tab.label}
                    </Link>
                );
            })}
        </nav>
    );
}
