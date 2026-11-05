import { LinkButton } from '@/components/Buttons';
import Icon from '@/components/Icon';
import Page from '@/layouts/Page';

/** Convite para mudar de plano, no lugar do Financeiro, para quem está num plano sem ele */
export default function FinanceUpgrade({ plan }: { plan: string }) {
    return (
        <Page title="Financeiro">
            <section className="cartao flex max-w-[640px] flex-col items-start gap-4 p-6 sm:p-8">
                <span className="flex h-12 w-12 items-center justify-center rounded-campo bg-pele text-cafe-escuro">
                    <Icon name="financeiro" className="h-[22px] w-[22px]" />
                </span>
                <h2 className="text-2xl font-semibold">O Financeiro faz parte dos planos Profissional e Premium</h2>
                <p className="text-[15px] leading-relaxed text-suave">
                    Sua empresa está no plano {plan}. Mudando de plano, você vê quanto recebeu no mês, as despesas a vencer, o lucro estimado e quanto pagar de comissão a cada
                    profissional. Os pagamentos que você já recebeu pela agenda ficam guardados.
                </p>
                <LinkButton href={route('plan.edit')}>Ver planos</LinkButton>
            </section>
        </Page>
    );
}
