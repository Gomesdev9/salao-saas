import { Head, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import BottomNav from './BottomNav';
import Sidebar from './Sidebar';

type Props = {
    /** Título da aba do navegador */
    title?: string;
    /** Ocupa a largura toda (usado na agenda) */
    wide?: boolean;
    children: ReactNode;
};

/** Moldura das telas da empresa: menu lateral no computador e menu de baixo no celular */
export default function AppLayout({ title, wide = false, children }: Props) {
    const { appName } = usePage().props;

    return (
        <>
            <Head title={title ? `${title} · ${appName}` : appName} />
            <div className="min-h-screen lg:flex">
                <Sidebar />

                <main className="min-w-0 flex-1 px-4 pb-32 pt-5 sm:px-6 lg:px-8 lg:pb-10 lg:pt-8">
                    <div className={wide ? '' : 'max-w-[1100px]'}>{children}</div>
                </main>
            </div>

            <BottomNav />
        </>
    );
}
