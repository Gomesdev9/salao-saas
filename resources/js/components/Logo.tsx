import { usePage } from '@inertiajs/react';
import { cx } from '@/lib/format';

export default function Logo({ subtitle, size = 'h-9 w-9 text-lg', className }: { subtitle?: string | null; size?: string; className?: string }) {
    const { appName } = usePage().props;

    return (
        <span className={cx('flex items-center gap-2.5', className)}>
            <span className={cx('flex items-center justify-center rounded-campo bg-caramelo font-bold text-white', size)} aria-hidden="true">
                S
            </span>
            <span className="flex flex-col">
                <span className="text-[15px] font-bold">{appName}</span>
                {subtitle && <span className="text-xs text-menu-suave">{subtitle}</span>}
            </span>
        </span>
    );
}
