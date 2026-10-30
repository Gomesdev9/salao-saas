import { router } from '@inertiajs/react';
import PlanCard from '@/components/PlanCard';
import Page from '@/layouts/Page';
import { cx } from '@/lib/format';
import type { PlanInfo } from '@/types';

type Props = {
    current: string;
    trial: { daysLeft: number; endsOn: string } | null;
    plans: PlanInfo[];
    activeProfessionals: number;
    errors: Record<string, string>;
};

/** "Meu plano": o plano da empresa, o teste grátis e a troca de plano */
export default function PlanEdit({ current, trial, plans, activeProfessionals, errors }: Props) {
    const plan = plans.find((item) => item.value === current)!;
    const limit = plan.professionalLimit;
    const over = limit !== null && activeProfessionals > limit;

    return (
        <Page title="Meu plano" subtitle="O plano da sua empresa e o que cada plano inclui.">
            {errors.plan && (
                <div role="alert" className="rounded-campo bg-mel px-4 py-3 text-sm font-medium text-mel-texto">
                    {errors.plan}
                </div>
            )}

            <section className="cartao flex flex-wrap items-center justify-between gap-6 p-6">
                <div className="flex flex-col gap-1">
                    <span className="text-[13px] font-semibold uppercase tracking-wider text-cafe">Plano atual</span>
                    <h2 className="text-[28px] font-semibold leading-tight">
                        {plan.label} <span className="font-sans text-lg font-bold">· {plan.formattedPrice}/mês</span>
                    </h2>
                    {trial ? (
                        <span className="text-sm text-suave">
                            Teste grátis: {trial.daysLeft === 1 ? 'falta' : 'faltam'}{' '}
                            <strong className="text-tinta">
                                {trial.daysLeft} {trial.daysLeft === 1 ? 'dia' : 'dias'}
                            </strong>{' '}
                            (até {trial.endsOn}). Você pode trocar de plano quando quiser.
                        </span>
                    ) : (
                        <span className="text-sm text-suave">Você pode trocar de plano quando quiser.</span>
                    )}
                </div>
                <div className="flex w-full flex-col gap-2 sm:w-[260px]">
                    <span className="flex justify-between gap-3 text-sm">
                        Profissionais ativos
                        <strong>
                            {activeProfessionals} de {limit ?? 'ilimitados'}
                        </strong>
                    </span>
                    {limit !== null && (
                        <>
                            <div className="h-2 overflow-hidden rounded-full bg-areia" role="presentation">
                                <div className={cx('h-full rounded-full', over ? 'bg-perigo' : 'bg-cafe')} style={{ width: `${Math.min(100, Math.round((activeProfessionals / limit) * 100))}%` }} />
                            </div>
                            {over && <span className="text-xs font-medium text-perigo">Acima do limite do plano: novos profissionais só entram como inativos.</span>}
                        </>
                    )}
                </div>
            </section>

            <div className="grid gap-6 pt-3 lg:grid-cols-3">
                {plans.map((item) => (
                    <PlanCard key={item.value} plan={item} current={item.value === current}>
                        {item.value === current ? (
                            <span
                                className={cx(
                                    'inline-flex h-12 w-full items-center justify-center rounded-full border text-[15px] font-semibold',
                                    item.highlighted ? 'border-menu-ativo text-menu-texto' : 'border-linha text-suave',
                                )}
                            >
                                Plano atual
                            </span>
                        ) : (
                            <button
                                type="button"
                                onClick={() => router.patch(route('plan.update'), { plan: item.value }, { preserveScroll: true })}
                                className={cx(
                                    'inline-flex h-12 w-full items-center justify-center rounded-full text-[15px] font-semibold transition',
                                    item.highlighted ? 'bg-pele text-tinta hover:bg-white' : 'bg-cafe text-white hover:bg-cafe-escuro',
                                )}
                            >
                                Mudar para o {item.label}
                            </button>
                        )}
                    </PlanCard>
                ))}
            </div>

            <p className="text-sm text-suave">O que está marcado como "Em breve" entra no plano assim que ficar pronto.</p>
        </Page>
    );
}
