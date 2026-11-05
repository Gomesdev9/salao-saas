import { cx } from '@/lib/format';

/** Selo "Ativo" (verde) ou "Inativo" (cinza) */
export default function ActiveBadge({ active }: { active: boolean }) {
    return <span className={cx('selo border-transparent', active ? 'bg-[#E7F4EC] text-[#1C5234]' : 'bg-[#F2EDE7] text-suave')}>{active ? 'Ativo' : 'Inativo'}</span>;
}
