import { cx } from '@/lib/format';

/** Círculo com as iniciais do nome, ex.: "Ana Souza" vira "AS" */
export default function Avatar({ name, size = 'h-[34px] w-[34px] text-xs', className }: { name: string; size?: string; className?: string }) {
    const initials = name
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase();

    return (
        <span className={cx('avatar', size, className)} aria-hidden="true">
            {initials}
        </span>
    );
}
