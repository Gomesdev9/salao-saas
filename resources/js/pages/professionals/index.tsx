import { Link } from '@inertiajs/react';
import ActiveBadge from '@/components/ActiveBadge';
import Avatar from '@/components/Avatar';
import { LinkButton } from '@/components/Buttons';
import Chip from '@/components/Chip';
import Page from '@/layouts/Page';

type Professional = {
    id: number;
    name: string;
    phone: string | null;
    active: boolean;
    commission: string;
    todayCount: number;
    services: string[];
};

type Props = {
    professionals: Professional[];
    showInactive: boolean;
    activeCount: number;
    inactiveCount: number;
};

export default function ProfessionalsIndex({ professionals, showInactive, activeCount, inactiveCount }: Props) {
    return (
        <Page
            title="Profissionais"
            subtitle="Quem atende no salão. Só os ativos aparecem na agenda."
            actions={
                <LinkButton href={route('professionals.create')} icon="mais">
                    Novo profissional
                </LinkButton>
            }
        >
            <div className="flex gap-2">
                <Chip href={route('professionals.index')} on={!showInactive}>
                    Ativos ({activeCount})
                </Chip>
                <Chip href={route('professionals.index', { inativos: 1 })} on={showInactive}>
                    Inativos ({inactiveCount})
                </Chip>
            </div>

            {professionals.length === 0 ? (
                <div className="cartao p-6 text-suave">{showInactive ? 'Nenhum profissional inativo.' : 'Nenhum profissional cadastrado ainda.'}</div>
            ) : (
                <div className="grid gap-4 [grid-template-columns:repeat(auto-fill,minmax(280px,1fr))]">
                    {professionals.map((professional) => (
                        <article key={professional.id} className="cartao flex flex-col gap-3.5 p-5">
                            <div className="flex items-center gap-3">
                                <Avatar name={professional.name} size="h-12 w-12 text-base" className={professional.active ? undefined : '!bg-[#F2EDE7] !text-suave'} />
                                <div className="flex min-w-0 flex-1 flex-col">
                                    <h2 className="truncate text-base font-bold">{professional.name}</h2>
                                    <span className="text-[13px] text-suave">{professional.phone || 'Sem telefone'}</span>
                                </div>
                                <ActiveBadge active={professional.active} />
                            </div>
                            <div className="flex gap-6">
                                <div className="flex flex-col gap-0.5">
                                    <span className="text-xs text-suave">Comissão</span>
                                    <span className="text-lg font-bold">{professional.commission}%</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="text-xs text-suave">Horários hoje</span>
                                    <span className="text-lg font-bold">{professional.active ? professional.todayCount : '—'}</span>
                                </div>
                            </div>
                            <div className="flex flex-wrap gap-1.5">
                                {professional.services.length > 0 ? (
                                    professional.services.map((service) => (
                                        <span key={service} className="rounded-full border border-linha bg-creme px-2.5 py-1 text-xs font-medium">
                                            {service}
                                        </span>
                                    ))
                                ) : (
                                    <span className="text-xs text-suave">Nenhum serviço vinculado</span>
                                )}
                            </div>
                            <div className="mt-auto flex gap-2 border-t border-linha-clara pt-3">
                                <Link href={route('professionals.edit', professional.id)} className="inline-flex min-h-11 flex-1 items-center justify-center rounded-campo border border-linha text-sm font-semibold hover:bg-areia">
                                    Editar
                                </Link>
                                <Link href={route('appointments.index')} className="inline-flex min-h-11 flex-1 items-center justify-center rounded-campo border border-linha text-sm font-semibold hover:bg-areia">
                                    Ver agenda
                                </Link>
                            </div>
                        </article>
                    ))}
                </div>
            )}
        </Page>
    );
}
