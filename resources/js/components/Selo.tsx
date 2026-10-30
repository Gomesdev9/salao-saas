import type { ReactNode } from 'react';
import { cx } from '@/lib/format';

/** Selo arredondado de situação. As cores vêm do Laravel (ex.: AppointmentStatus::color()). */
export default function Selo({ color, className, children }: { color: string; className?: string; children: ReactNode }) {
    return <span className={cx('selo', color, className)}>{children}</span>;
}
