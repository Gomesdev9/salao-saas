import { Head, Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import Logo from '@/components/Logo';

/** Login, cadastro e senha: painel escuro com a apresentação e o formulário ao lado */
export default function GuestLayout({ title, aside, children }: { title?: string; aside?: ReactNode; children: ReactNode }) {
    const { appName } = usePage().props;

    return (
        <>
            <Head title={title ? `${title} · ${appName}` : appName} />
            <div className="flex min-h-screen flex-wrap">
                <section className="flex flex-[1_1_420px] flex-col justify-between gap-10 bg-tinta p-8 text-creme sm:p-12">
                    <Link href="/">
                        <Logo size="h-10 w-10 text-xl" />
                    </Link>
                    <div className="flex max-w-[460px] flex-col gap-6">
                        {aside ?? (
                            <h1 className="text-4xl font-bold leading-tight sm:text-[40px]">
                                A agenda do seu salão, <em className="italic text-[#D9A57F]">organizada</em>.
                            </h1>
                        )}
                    </div>
                    <span className="text-[13px] text-menu-suave">Cada empresa vê só os próprios dados.</span>
                </section>

                <section className="flex flex-[1_1_440px] items-center justify-center px-4 py-10 sm:px-6 sm:py-12">
                    <div className="w-full max-w-[460px] rounded-[28px] border border-linha bg-cartao p-6 shadow-cartao sm:p-9">{children}</div>
                </section>
            </div>
        </>
    );
}

/** Aviso verde (ex.: "Enviamos o link para o seu e-mail") */
export function SessionStatus({ status, className }: { status?: string | null; className?: string }) {
    return status ? <div className={`rounded-campo bg-[#E7F4EC] px-4 py-3 text-sm font-medium text-[#1C5234] ${className ?? ''}`}>{status}</div> : null;
}
