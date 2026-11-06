import { Link, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import Avatar from '@/components/Avatar';
import Icon from '@/components/Icon';
import { cx } from '@/lib/format';
import { bottomItems, isActive, mainItems, type NavItem } from './nav';
import { LogoutButton } from './Sidebar';

// Telas que ficam dentro do "Mais"
const more: NavItem[] = [
    ...mainItems.filter((item) => !bottomItems.includes(item)),
    { route: 'plan.edit', active: 'plan.*', icon: 'plano', label: 'Meu plano' },
];

/** Menu flutuante do celular, no rodapé da tela */
export default function BottomNav() {
    const { user } = usePage().props.auth!;
    const [open, setOpen] = useState(false);
    const box = useRef<HTMLDivElement>(null);
    const moreActive = isActive('booking-page.*', 'professionals.*', 'services.*', 'finance.*', 'plan.*', 'profile.*');

    // Fecha com Esc ou tocando fora do menu
    useEffect(() => {
        const onKey = (event: KeyboardEvent) => event.key === 'Escape' && setOpen(false);
        const onClick = (event: MouseEvent) => box.current && !box.current.contains(event.target as Node) && setOpen(false);
        window.addEventListener('keydown', onKey);
        document.addEventListener('click', onClick);

        return () => {
            window.removeEventListener('keydown', onKey);
            document.removeEventListener('click', onClick);
        };
    }, []);

    return (
        <div ref={box} className="fixed inset-x-3.5 bottom-3.5 z-40 lg:hidden">
            {open && (
                <div id="menu-mais" className="mb-2 flex flex-col gap-1 vidro-escuro rounded-[26px] p-2 text-creme">
                    {more.map((item) => {
                        const active = isActive(item.active);

                        return (
                            <Link
                                key={item.route}
                                href={route(item.route)}
                                onClick={() => setOpen(false)}
                                aria-current={active ? 'page' : undefined}
                                className={cx('flex min-h-12 items-center gap-3 rounded-full px-4 text-sm', active ? 'vidro-ativo font-semibold text-white' : 'font-medium text-menu-texto')}
                            >
                                <Icon name={item.icon} className="h-[18px] w-[18px]" />
                                {item.label}
                            </Link>
                        );
                    })}
                    <div className="flex items-center gap-2.5 border-t border-white/10 px-2 pt-2">
                        <Avatar name={user.name} size="h-8 w-8 text-xs" className="!bg-menu-ativo !text-creme" />
                        <Link href={route('profile.edit')} onClick={() => setOpen(false)} className="flex min-h-11 min-w-0 flex-1 items-center truncate text-[13px] text-menu-texto">
                            {user.name}
                        </Link>
                        <LogoutButton />
                    </div>
                </div>
            )}

            <nav aria-label="Menu principal" className="vidro-escuro grid h-[68px] grid-cols-4 rounded-full">
                {bottomItems.map((item) => {
                    const active = isActive(item.active);

                    return (
                        <Link
                            key={item.route}
                            href={route(item.route)}
                            aria-current={active ? 'page' : undefined}
                            className={cx(
                                'flex flex-col items-center justify-center gap-1 rounded-full text-[11px]',
                                active ? 'vidro-ativo m-1.5 font-bold text-white' : 'font-semibold text-menu-texto',
                            )}
                        >
                            <Icon name={item.icon} className="h-[22px] w-[22px]" />
                            {item.label}
                        </Link>
                    );
                })}
                <button
                    type="button"
                    onClick={() => setOpen(!open)}
                    aria-expanded={open}
                    aria-controls="menu-mais"
                    className={cx(
                        'flex flex-col items-center justify-center gap-1 rounded-full text-[11px]',
                        moreActive ? 'vidro-ativo m-1.5 font-bold text-white' : 'font-semibold text-menu-texto',
                    )}
                >
                    <Icon name="menu" className="h-[22px] w-[22px]" />
                    Mais
                </button>
            </nav>
        </div>
    );
}
