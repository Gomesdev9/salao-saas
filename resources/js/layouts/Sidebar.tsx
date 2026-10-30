import { Link, usePage } from '@inertiajs/react';
import Avatar from '@/components/Avatar';
import Icon from '@/components/Icon';
import Logo from '@/components/Logo';
import { cx } from '@/lib/format';
import { isActive, mainItems } from './nav';

/** Menu lateral flutuante (computador). No celular quem aparece é o menu de baixo (BottomNav). */
export default function Sidebar() {
    const { user, salon } = usePage().props.auth!;
    const planActive = isActive('plan.*');

    return (
        <aside className="m-4 hidden w-[236px] shrink-0 flex-col gap-7 rounded-menu bg-tinta px-4 py-6 text-creme shadow-menu lg:sticky lg:top-4 lg:flex lg:h-[calc(100vh-2rem)]">
            <Link href={route('dashboard')} className="min-w-0 px-2">
                <Logo subtitle={salon.name} />
            </Link>

            <div className="flex flex-1 flex-col gap-7">
                <nav aria-label="Menu principal" className="flex flex-col gap-1">
                    {mainItems.map((item) => {
                        const active = isActive(item.active);

                        return (
                            <Link
                                key={item.route}
                                href={route(item.route)}
                                aria-current={active ? 'page' : undefined}
                                className={cx(
                                    'flex min-h-11 items-center gap-3 rounded-full px-3 text-sm transition',
                                    active ? 'bg-menu-ativo font-semibold text-white' : 'font-medium text-menu-texto hover:bg-menu-ativo/60 hover:text-white',
                                )}
                            >
                                <Icon name={item.icon} className="h-[18px] w-[18px]" />
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>

                {/* Plano da empresa e dias que faltam do teste grátis */}
                <Link
                    href={route('plan.edit')}
                    aria-current={planActive ? 'page' : undefined}
                    className={cx('mt-auto flex items-center gap-3 rounded-[18px] px-3 py-2.5 transition', planActive ? 'bg-menu-ativo' : 'bg-menu-ativo/50 hover:bg-menu-ativo')}
                >
                    <Icon name="plano" className="h-[18px] w-[18px] text-[#D9A57F]" />
                    <span className="flex min-w-0 flex-col">
                        <span className="text-[13px] font-semibold text-white">Plano {salon.plan}</span>
                        <span className="text-xs text-menu-suave">
                            {salon.onTrial ? `Teste grátis · ${salon.trialDaysLeft} ${salon.trialDaysLeft === 1 ? 'dia' : 'dias'}` : 'Ver meu plano'}
                        </span>
                    </span>
                </Link>

                <div className="flex items-center gap-2.5 border-t border-menu-ativo px-2 pt-3">
                    <Avatar name={user.name} size="h-8 w-8 text-xs" className="!bg-menu-ativo !text-creme" />
                    <Link href={route('profile.edit')} className="min-w-0 flex-1 truncate text-[13px] text-menu-texto hover:text-white" title="Meu perfil">
                        {user.name}
                    </Link>
                    <LogoutButton />
                </div>
            </div>
        </aside>
    );
}

export function LogoutButton() {
    return (
        <Link
            href={route('logout')}
            method="post"
            as="button"
            className="flex h-11 w-11 items-center justify-center rounded-full text-menu-texto hover:bg-menu-ativo hover:text-white"
        >
            <span className="sr-only">Sair</span>
            <Icon name="sair" className="h-[18px] w-[18px]" />
        </Link>
    );
}
