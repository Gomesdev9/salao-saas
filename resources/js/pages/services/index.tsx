import { Link } from '@inertiajs/react';
import ActiveBadge from '@/components/ActiveBadge';
import { LinkButton } from '@/components/Buttons';
import Chip from '@/components/Chip';
import Page from '@/layouts/Page';
import { duration } from '@/lib/duration';
import { money, plural } from '@/lib/format';

type Service = { id: number; name: string; duration_minutes: number; price: number; active: boolean };

type Props = {
    categories: string[];
    category: string | null;
    groups: { name: string; services: Service[] }[];
};

export default function ServicesIndex({ categories, category, groups }: Props) {
    return (
        <Page
            title="Serviços"
            subtitle="A duração define quanto tempo o horário ocupa na agenda."
            actions={
                <LinkButton href={route('services.create')} icon="mais">
                    Novo serviço
                </LinkButton>
            }
        >
            {groups.length === 0 ? (
                <div className="cartao p-6 text-suave">Nenhum serviço cadastrado ainda.</div>
            ) : (
                <>
                    {categories.length > 1 && (
                        <nav className="flex flex-wrap gap-2" aria-label="Filtrar por categoria">
                            {[null, ...categories].map((item) => (
                                <Chip key={item ?? ''} href={route('services.index', item ? { categoria: item } : {})} on={category === item}>
                                    {item ?? 'Todos'}
                                </Chip>
                            ))}
                        </nav>
                    )}

                    {groups.map((group) => (
                        <section key={group.name} className="cartao overflow-hidden">
                            <div className="flex items-center justify-between border-b border-linha bg-areia px-5 py-3.5">
                                <h2 className="text-base font-bold">{group.name}</h2>
                                <span className="text-[13px] text-suave">{plural(group.services.length, 'serviço', 'serviços')}</span>
                            </div>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[560px] table-fixed border-collapse text-sm">
                                    <colgroup>
                                        <col />
                                        <col className="w-[18%]" />
                                        <col className="w-[18%]" />
                                        <col className="w-[16%]" />
                                        <col className="w-[100px]" />
                                    </colgroup>
                                    <thead>
                                        <tr className="text-left text-xs uppercase tracking-wider text-suave">
                                            <th className="px-5 py-2.5 font-semibold">Serviço</th>
                                            <th className="px-3 py-2.5 font-semibold">Duração</th>
                                            <th className="px-3 py-2.5 font-semibold">Preço</th>
                                            <th className="px-3 py-2.5 font-semibold">Situação</th>
                                            <th className="px-5 py-2.5">
                                                <span className="sr-only">Ações</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {group.services.map((service) => (
                                            <tr key={service.id} className="border-t border-linha-clara">
                                                <td className="px-5 py-3 font-semibold">{service.name}</td>
                                                <td className="px-3 py-3">{duration(service.duration_minutes)}</td>
                                                <td className="px-3 py-3 font-semibold">{money(service.price)}</td>
                                                <td className="px-3 py-3">
                                                    <ActiveBadge active={service.active} />
                                                </td>
                                                <td className="px-5 py-1.5 text-right">
                                                    <Link href={route('services.edit', service.id)} className="inline-flex min-h-11 items-center font-semibold text-cafe hover:text-cafe-escuro">
                                                        Editar
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    ))}
                </>
            )}
        </Page>
    );
}
