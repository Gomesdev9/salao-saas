import { Link } from '@inertiajs/react';

export type Paginated<T> = {
    data: T[];
    from: number | null;
    to: number | null;
    total: number;
    currentPage: number;
    prevUrl: string | null;
    nextUrl: string | null;
};

/** Anterior · página atual · Próxima (só aparece quando há mais de uma página) */
export default function Pagination({ page }: { page: Paginated<unknown> }) {
    if (!page.prevUrl && !page.nextUrl) {
        return null;
    }

    const button = 'inline-flex h-11 items-center rounded-campo border border-linha px-3.5 font-semibold text-tinta hover:bg-areia';

    return (
        <div className="flex gap-1.5 text-sm">
            {page.prevUrl && (
                <Link href={page.prevUrl} className={button}>
                    Anterior
                </Link>
            )}
            <span className="inline-flex h-11 items-center rounded-campo bg-tinta px-4 font-semibold text-white" aria-current="page">
                {page.currentPage}
            </span>
            {page.nextUrl && (
                <Link href={page.nextUrl} className={button}>
                    Próxima
                </Link>
            )}
        </div>
    );
}
