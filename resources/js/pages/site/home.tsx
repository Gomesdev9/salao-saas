import { Head, Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import Icon from '@/components/Icon';
import Logo from '@/components/Logo';
import PlanCard from '@/components/PlanCard';
import { cx } from '@/lib/format';
import type { PlanInfo, Status } from '@/types';

type Props = {
    plans: PlanInfo[];
    trialDays: number;
    businessTypes: string[];
    statuses: Record<string, Status>;
    year: number;
};

const features = [
    { icon: 'agenda', title: 'Agenda por profissional', text: 'O dia inteiro numa tela, com uma coluna para cada profissional. O sistema não deixa marcar dois horários ao mesmo tempo com a mesma pessoa.' },
    { icon: 'clientes', title: 'Clientes', text: 'WhatsApp, aniversário e observações de cada cliente, com busca por nome ou telefone. Os aniversariantes da semana aparecem no painel.' },
    { icon: 'servicos', title: 'Serviços', text: 'Categoria, duração e preço de cada serviço. Ao agendar, o horário de término e o valor já saem do serviço escolhido.' },
    { icon: 'profissionais', title: 'Profissionais e comissões', text: 'Cadastre a equipe, os serviços que cada profissional faz e a porcentagem de comissão de cada um.' },
    { icon: 'painel', title: 'Painel do dia', text: 'Assim que você entra: atendimentos de hoje, valor previsto e os horários que ainda faltam confirmar.' },
    {
        icon: 'financeiro',
        title: 'Financeiro e caixa',
        text: 'Receba no Pix, dinheiro ou cartão direto do horário, acompanhe as despesas a vencer, o lucro do mês e quanto pagar de comissão a cada profissional.',
    },
    {
        icon: 'link',
        title: 'Agendamento online',
        text: 'Um link da sua empresa para a cliente escolher o procedimento, o profissional e um horário livre. O horário cai direto na agenda, e ela remarca ou cancela pelo celular.',
    },
    { icon: 'cadeado', title: 'Dados só seus', text: 'Cada empresa tem a própria conta e só enxerga os próprios clientes, profissionais e horários.' },
];

const comingSoon = [{ icon: 'whatsapp', title: 'Lembretes por WhatsApp' }];

const steps = [
    { title: 'Cadastre sua empresa', text: 'Nome, tipo de negócio, WhatsApp e os seus dados de acesso. Ao terminar, você já entra no sistema.' },
    { title: 'Monte serviços e equipe', text: 'Cadastre os serviços com preço e duração e os profissionais que atendem cada um.' },
    { title: 'Comece a agendar', text: 'Marque os horários na agenda e acompanhe o dia pelo painel, no computador ou no celular.' },
];

const faq = (trialDays: number) => [
    { q: 'Preciso instalar algum programa?', a: 'Não. O sistema funciona no navegador, tanto no computador quanto no celular.' },
    { q: 'Outras empresas conseguem ver os meus dados?', a: 'Não. Cada empresa tem a própria conta, e o sistema separa os dados de cada uma: clientes, profissionais, serviços e horários.' },
    { q: 'Como funciona o teste grátis?', a: `Você escolhe o plano no cadastro e usa o sistema completo por ${trialDays} dias, sem cartão de crédito.` },
    { q: 'Posso trocar de plano depois?', a: 'Pode. Na tela "Meu plano" você muda para outro plano quando quiser, inclusive durante o teste.' },
    { q: 'Quantos profissionais posso cadastrar?', a: 'No Essencial, até 2 profissionais ativos. No Profissional, até 6. No Premium não tem limite. Profissionais inativos não contam.' },
    { q: 'Quem faz o cadastro da empresa?', a: 'O proprietário. Quem cadastra a empresa vira o administrador da conta e entra com o próprio e-mail e senha.' },
];

// Horários de exemplo da prévia da agenda: [hora, profissional (0 ou 1), cliente, serviço, situação]
const preview: [string, 0 | 1, string, string, string][] = [
    ['09:00', 0, 'Débora', 'Escova', 'done'],
    ['09:30', 1, 'Carla', 'Manicure', 'confirmed'],
    ['10:30', 0, 'Júlia', 'Corte feminino', 'confirmed'],
    ['11:00', 1, 'Renata', 'Pé e mão', 'scheduled'],
    ['13:30', 0, 'Paula', 'Coloração', 'scheduled'],
];

const sections = [
    ['recursos', 'Recursos'],
    ['como-funciona', 'Como funciona'],
    ['para-quem', 'Para quem é'],
    ['planos', 'Planos'],
    ['duvidas', 'Dúvidas'],
];

function SectionTitle({ eyebrow, children, text }: { eyebrow: string; children: ReactNode; text?: ReactNode }) {
    return (
        <>
            <span className="text-[13px] font-bold uppercase tracking-[0.16em] text-cafe">{eyebrow}</span>
            <h2 className="text-[32px] font-semibold leading-tight sm:text-[40px] [&_em]:italic [&_em]:text-cafe">{children}</h2>
            {text && <p className="text-base leading-relaxed text-suave">{text}</p>}
        </>
    );
}

const Arrow = () => <Icon name="seta" className="h-[18px] w-[18px]" />;

/** Página de divulgação do sistema, com o convite para a empresa se cadastrar */
export default function Home({ plans, trialDays, businessTypes, statuses, year }: Props) {
    const { appName, auth } = usePage().props;
    const signedIn = auth !== null;

    return (
        <>
            <Head title={`${appName} · Sistema para salões e clínicas de estética`}>
                <meta name="description" content="Agenda, clientes, serviços e comissões da sua empresa de beleza num só lugar. Cadastre sua empresa e comece a usar." />
            </Head>

            {/* Menu flutuante */}
            <header className="sticky top-0 z-30 px-3 pt-3 sm:px-6">
                <div className="mx-auto flex max-w-[1180px] items-center justify-between gap-2 rounded-full border border-linha bg-cartao/95 py-1.5 pl-4 pr-1.5 shadow-cartao backdrop-blur sm:pl-5">
                    <Link href={route('home')} className="min-w-0">
                        <Logo />
                    </Link>
                    <nav aria-label="Seções da página" className="hidden items-center gap-1 lg:flex">
                        {sections.map(([anchor, label]) => (
                            <a key={anchor} href={`#${anchor}`} className="inline-flex h-11 items-center rounded-full px-4 text-sm font-semibold text-suave transition hover:bg-areia hover:text-tinta">
                                {label}
                            </a>
                        ))}
                    </nav>
                    <div className="flex shrink-0 items-center gap-1">
                        {signedIn ? (
                            <Link href={route('dashboard')} className="inline-flex h-11 items-center gap-2 rounded-full bg-cafe px-5 text-sm font-semibold text-white transition hover:bg-cafe-escuro">
                                Ir para o painel
                            </Link>
                        ) : (
                            <>
                                <Link href={route('login')} className="inline-flex h-11 items-center rounded-full px-3 text-sm font-semibold transition hover:bg-areia sm:px-4">
                                    Entrar
                                </Link>
                                <Link href={route('register')} className="inline-flex h-11 items-center rounded-full bg-cafe px-4 text-sm font-semibold text-white transition hover:bg-cafe-escuro sm:px-5">
                                    Cadastrar<span className="hidden sm:inline">&nbsp;empresa</span>
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </header>

            <main className="overflow-x-clip">
                {/* Abertura */}
                <section className="mx-auto grid max-w-[1180px] items-center gap-12 px-4 pb-16 pt-12 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:pb-24 lg:pt-20">
                    <div className="flex flex-col gap-6">
                        <span className="text-[13px] font-bold uppercase tracking-[0.16em] text-cafe">Sistema para salões e clínicas de estética</span>
                        <h1 className="text-[40px] font-semibold leading-[1.05] sm:text-[56px]">
                            A agenda da sua empresa, <em className="italic text-cafe">organizada</em> do primeiro ao último horário.
                        </h1>
                        <p className="max-w-[540px] text-lg leading-relaxed text-suave">
                            Agenda por profissional, clientes, serviços e comissões num só lugar. Funciona no navegador do computador e do celular, sem instalar nada.
                        </p>
                        <div className="flex flex-wrap gap-3">
                            {signedIn ? (
                                <Link
                                    href={route('dashboard')}
                                    className="inline-flex h-14 items-center gap-2 rounded-full bg-cafe px-7 text-base font-semibold text-white shadow-[0_14px_28px_-14px_rgba(146,88,55,0.8)] transition hover:bg-cafe-escuro"
                                >
                                    Ir para o painel
                                    <Arrow />
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('register')}
                                        className="inline-flex h-14 items-center gap-2 rounded-full bg-cafe px-7 text-base font-semibold text-white shadow-[0_14px_28px_-14px_rgba(146,88,55,0.8)] transition hover:bg-cafe-escuro"
                                    >
                                        Cadastrar minha empresa
                                        <Arrow />
                                    </Link>
                                    <Link href={route('login')} className="inline-flex h-14 items-center rounded-full border border-linha-forte bg-cartao px-7 text-base font-semibold transition hover:bg-areia">
                                        Já tenho conta
                                    </Link>
                                </>
                            )}
                        </div>
                        <ul className="flex flex-wrap gap-x-6 gap-y-2 text-sm text-suave">
                            {[`Teste grátis por ${trialDays} dias`, 'Sem instalar nada', 'Dados separados por empresa'].map((point) => (
                                <li key={point} className="flex items-center gap-2">
                                    <Icon name="check" className="h-4 w-4 text-cafe" />
                                    {point}
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Prévia da agenda, feita em HTML com as cores reais das situações */}
                    <div className="relative mx-auto w-full max-w-[520px]">
                        <div className="absolute -inset-3 -rotate-2 rounded-[36px] bg-pele/70 sm:-inset-5" aria-hidden="true" />
                        <figure className="cartao relative overflow-hidden" role="img" aria-label="Exemplo da agenda do dia com horários de duas profissionais">
                            <div className="flex items-center justify-between gap-3 border-b border-linha px-5 py-4">
                                <div className="flex flex-col">
                                    <span className="text-xs font-semibold uppercase tracking-wider text-cafe">Agenda de hoje</span>
                                    <span className="font-display text-xl font-semibold">Quarta, 21 de outubro</span>
                                </div>
                                <span className="selo border-[#3E9A63] bg-[#E7F4EC] text-[#1C5234]">5 horários</span>
                            </div>
                            <div className="grid grid-cols-2 border-b border-linha bg-areia text-sm font-semibold">
                                {['Ana', 'Bruna'].map((professional, i) => (
                                    <div key={professional} className={cx('flex items-center gap-2 px-4 py-2.5', i === 0 && 'border-r border-linha')}>
                                        <span className="avatar h-7 w-7 text-xs">{professional.charAt(0)}</span>
                                        {professional}
                                    </div>
                                ))}
                            </div>
                            <div className="flex flex-col gap-2 p-4">
                                {preview.map(([time, column, client, service, status]) => (
                                    <div key={time} className="grid grid-cols-2 gap-3">
                                        <div className={cx(column === 1 && 'col-start-2', 'flex flex-col gap-0.5 rounded-campo border-l-4 px-3 py-2', statuses[status].color)}>
                                            <span className="flex items-center justify-between gap-2 text-xs font-bold">
                                                {time}
                                                <span className="font-semibold">{statuses[status].label}</span>
                                            </span>
                                            <span className="truncate text-sm font-semibold text-tinta">{client}</span>
                                            <span className="truncate text-xs text-suave">{service}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </figure>
                        <div className="absolute -bottom-6 right-4 flex items-center gap-3 rounded-[18px] bg-tinta px-4 py-3 text-creme shadow-menu sm:-right-6" aria-hidden="true">
                            <span className="flex h-9 w-9 items-center justify-center rounded-full bg-menu-ativo text-[#D9A57F]">
                                <Icon name="financeiro" className="h-[18px] w-[18px]" />
                            </span>
                            <span className="flex flex-col">
                                <span className="text-xs text-menu-suave">Previsto hoje</span>
                                <span className="text-base font-bold">R$ 420,00</span>
                            </span>
                        </div>
                    </div>
                </section>

                {/* Recursos */}
                <section id="recursos" className="mx-auto flex max-w-[1180px] scroll-mt-24 flex-col gap-10 px-4 py-16 sm:px-6">
                    <div className="flex max-w-[640px] flex-col gap-3">
                        <SectionTitle eyebrow="Recursos" text="Do primeiro cadastro ao último atendimento do dia, sem planilha e sem caderno.">
                            Tudo o que o <em>dia a dia</em> da sua empresa precisa
                        </SectionTitle>
                    </div>
                    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {features.map((feature, i) => (
                            // Quando sobra um cartão sozinho na última linha, ele ocupa a linha inteira
                            <li
                                key={feature.title}
                                className={cx('cartao flex flex-col gap-3 p-6', i === features.length - 1 && features.length % 3 === 1 && 'sm:col-span-2 lg:col-span-3')}
                            >
                                <span className="flex h-12 w-12 items-center justify-center rounded-campo bg-pele text-cafe-escuro">
                                    <Icon name={feature.icon} className="h-[22px] w-[22px]" />
                                </span>
                                <h3 className="text-lg font-bold">{feature.title}</h3>
                                <p className="text-[15px] leading-relaxed text-suave">{feature.text}</p>
                            </li>
                        ))}
                    </ul>
                    <div className="flex flex-col gap-3">
                        <h3 className="text-sm font-bold uppercase tracking-wider text-suave">Em breve</h3>
                        <ul className="flex flex-wrap gap-3">
                            {comingSoon.map((item) => (
                                <li key={item.title} className="flex min-h-12 items-center gap-2.5 rounded-full border border-dashed border-linha-forte bg-cartao px-4 text-sm font-semibold">
                                    <Icon name={item.icon} className="h-[18px] w-[18px] text-cafe" />
                                    {item.title}
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>

                {/* Como funciona */}
                <section id="como-funciona" className="scroll-mt-24 px-3 py-8 sm:px-6">
                    <div className="mx-auto flex max-w-[1180px] flex-col gap-10 rounded-[36px] bg-tinta px-6 py-12 text-creme shadow-menu sm:px-12 sm:py-16">
                        <div className="flex max-w-[760px] flex-col gap-3">
                            <span className="text-[13px] font-bold uppercase tracking-[0.16em] text-[#D9A57F]">Como funciona</span>
                            <h2 className="text-[32px] font-semibold leading-tight sm:text-[40px]">
                                Do cadastro à agenda em <em className="italic text-[#D9A57F]">três passos</em>
                            </h2>
                        </div>
                        <ol className="grid gap-6 md:grid-cols-3">
                            {steps.map((step, i) => (
                                <li key={step.title} className="flex flex-col gap-3 rounded-cartao bg-[#2D1A10] p-6">
                                    <span className="flex h-10 w-10 items-center justify-center rounded-full bg-cafe text-base font-bold text-white">{i + 1}</span>
                                    <h3 className="text-lg font-bold">{step.title}</h3>
                                    <p className="text-[15px] leading-relaxed text-menu-texto">{step.text}</p>
                                </li>
                            ))}
                        </ol>
                        {!signedIn && (
                            <Link href={route('register')} className="inline-flex h-14 items-center gap-2 self-start rounded-full bg-pele px-7 text-base font-semibold text-tinta transition hover:bg-white">
                                Começar o cadastro
                                <Arrow />
                            </Link>
                        )}
                    </div>
                </section>

                {/* Para quem é */}
                <section id="para-quem" className="mx-auto flex max-w-[1180px] scroll-mt-24 flex-col gap-8 px-4 py-16 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex max-w-[520px] flex-col gap-3">
                        <SectionTitle eyebrow="Para quem é">
                            Feito para negócios de beleza que <em>vivem de agenda</em>
                        </SectionTitle>
                    </div>
                    <ul className="flex max-w-[560px] flex-wrap gap-3">
                        {businessTypes.map((type) => (
                            <li key={type} className="flex min-h-12 items-center rounded-full border border-linha bg-cartao px-5 text-[15px] font-semibold shadow-cartao">
                                {type}
                            </li>
                        ))}
                    </ul>
                </section>

                {/* Planos */}
                <section id="planos" className="mx-auto flex max-w-[1180px] scroll-mt-24 flex-col gap-10 px-4 py-16 sm:px-6">
                    <div className="flex max-w-[680px] flex-col gap-3">
                        <SectionTitle eyebrow="Planos" text={`Todos começam com ${trialDays} dias grátis, sem cartão de crédito. Você pode trocar de plano quando quiser.`}>
                            Um plano do <em>tamanho da sua equipe</em>
                        </SectionTitle>
                    </div>
                    <div className="grid gap-6 pt-3 lg:grid-cols-3">
                        {plans.map((plan) => {
                            const button = cx(
                                'inline-flex h-12 w-full items-center justify-center rounded-full text-[15px] font-semibold transition',
                                plan.highlighted ? 'bg-pele text-tinta hover:bg-white' : 'bg-cafe text-white hover:bg-cafe-escuro',
                            );

                            return (
                                <PlanCard key={plan.value} plan={plan}>
                                    {signedIn ? (
                                        <Link href={route('plan.edit')} className={button}>
                                            Ver meu plano
                                        </Link>
                                    ) : (
                                        <Link href={route('register', { plano: plan.value })} className={button}>
                                            Testar grátis por {trialDays} dias
                                        </Link>
                                    )}
                                </PlanCard>
                            );
                        })}
                    </div>
                </section>

                {/* Dúvidas */}
                <section id="duvidas" className="mx-auto flex max-w-[860px] scroll-mt-24 flex-col gap-8 px-4 py-16 sm:px-6">
                    <div className="flex flex-col gap-3 text-center">
                        <SectionTitle eyebrow="Dúvidas">Perguntas frequentes</SectionTitle>
                    </div>
                    <div className="flex flex-col gap-3">
                        {faq(trialDays).map((item, i) => (
                            <details key={item.q} className="cartao group px-6 py-1" open={i === 0}>
                                <summary className="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 text-base font-bold [&::-webkit-details-marker]:hidden">
                                    {item.q}
                                    <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pele text-cafe-escuro transition group-open:rotate-45">
                                        <Icon name="mais" className="h-4 w-4" />
                                    </span>
                                </summary>
                                <p className="pb-5 text-[15px] leading-relaxed text-suave">{item.a}</p>
                            </details>
                        ))}
                    </div>
                </section>

                {/* Chamada final */}
                <section className="px-3 pb-12 pt-4 sm:px-6">
                    <div className="mx-auto flex max-w-[1180px] flex-col items-start gap-6 rounded-[36px] bg-pele px-6 py-12 sm:px-12 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex max-w-[620px] flex-col gap-3">
                            <h2 className="text-[32px] font-semibold leading-tight sm:text-[40px]">
                                Vamos organizar a <em className="italic text-cafe">sua agenda</em>?
                            </h2>
                            <p className="text-base leading-relaxed text-suave">Cadastre a sua empresa, entre no sistema na mesma hora e use {trialDays} dias grátis.</p>
                        </div>
                        <Link
                            href={signedIn ? route('dashboard') : route('register')}
                            className="inline-flex h-14 shrink-0 items-center gap-2 rounded-full bg-tinta px-7 text-base font-semibold text-creme transition hover:bg-menu-ativo"
                        >
                            {signedIn ? 'Ir para o painel' : 'Cadastrar minha empresa'}
                            <Arrow />
                        </Link>
                    </div>
                </section>
            </main>

            <footer className="mx-auto flex max-w-[1180px] flex-wrap items-center justify-between gap-4 border-t border-linha px-4 py-8 text-sm text-suave sm:px-6">
                <Logo className="text-tinta" />
                <span>
                    © {year} {appName}
                </span>
                <nav aria-label="Acesso" className="flex gap-1">
                    <Link href={route('login')} className="inline-flex h-11 items-center rounded-full px-4 font-semibold text-tinta hover:bg-areia">
                        Entrar
                    </Link>
                    <Link href={route('register')} className="inline-flex h-11 items-center rounded-full px-4 font-semibold text-cafe hover:bg-areia">
                        Cadastrar empresa
                    </Link>
                </nav>
            </footer>
        </>
    );
}
