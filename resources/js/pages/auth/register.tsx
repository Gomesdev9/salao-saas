import { Link, useForm } from '@inertiajs/react';
import { PrimaryButton } from '@/components/Buttons';
import { InputError, InputLabel, SelectInput, TextInput } from '@/components/Form';
import Icon from '@/components/Icon';
import GuestLayout from '@/layouts/GuestLayout';
import { cx } from '@/lib/format';
import type { Option, PlanInfo } from '@/types';

type Props = {
    businessTypes: Option[];
    plans: PlanInfo[];
    /** Plano escolhido na página de divulgação (?plano=premium) ou o mais indicado */
    selectedPlan: string;
    trialDays: number;
};

const legend = 'pb-2.5 text-[13px] font-bold uppercase tracking-wider text-cafe';

/** Cadastro de uma nova empresa junto com o usuário proprietário */
export default function Register({ businessTypes, plans, selectedPlan, trialDays }: Props) {
    const form = useForm({
        plan: selectedPlan,
        salon_name: '',
        business_type: '',
        phone: '',
        city: '',
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });
    const { data, setData, errors } = form;
    const input = (field: keyof typeof data) => ({
        id: field,
        value: data[field],
        onChange: (e: React.ChangeEvent<HTMLInputElement>) => setData(field, e.target.value),
        className: 'h-12',
    });

    return (
        <GuestLayout
            title="Cadastre sua empresa"
            aside={
                <>
                    <h1 className="text-4xl font-bold leading-tight">Comece em três passos</h1>
                    <ol className="flex flex-col gap-4 text-base text-menu-texto">
                        {['Cadastre sua empresa', 'Cadastre serviços e profissionais', 'Comece a agendar'].map((step, i) => (
                            <li key={step} className="flex items-center gap-3.5">
                                <span className={cx('flex h-8 w-8 shrink-0 items-center justify-center rounded-full font-bold', i === 0 ? 'bg-cafe text-white' : 'border border-[#5A4334]')}>{i + 1}</span>
                                {step}
                            </li>
                        ))}
                    </ol>
                    <p className="flex items-center gap-2.5 text-[15px] text-menu-texto">
                        <Icon name="check" className="h-[18px] w-[18px] text-[#D9A57F]" />
                        {trialDays} dias grátis em qualquer plano, sem cartão.
                    </p>
                </>
            }
        >
            <form
                className="flex flex-col gap-5"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') });
                }}
            >
                <div className="flex flex-col gap-1.5">
                    <h2 className="text-[32px] font-semibold leading-tight">Cadastre sua empresa</h2>
                    <span className="text-[15px] text-suave">Você será o proprietário e administrador da conta.</span>
                </div>

                <fieldset className="flex flex-col gap-2.5">
                    <legend className={legend}>O plano</legend>
                    {plans.map((plan) => (
                        <label
                            key={plan.value}
                            className="flex min-h-14 cursor-pointer items-center gap-3 rounded-campo border border-linha bg-creme px-4 py-2.5 transition hover:border-linha-forte has-[:checked]:border-cafe has-[:checked]:bg-pele/50 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-cafe"
                        >
                            <input
                                type="radio"
                                name="plan"
                                value={plan.value}
                                checked={data.plan === plan.value}
                                onChange={() => setData('plan', plan.value)}
                                required
                                className="h-[18px] w-[18px] shrink-0 border-linha-forte text-cafe focus:ring-caramelo"
                            />
                            <span className="flex min-w-0 flex-1 flex-col">
                                <span className="flex flex-wrap items-center gap-x-2 text-[15px] font-bold">
                                    {plan.label}
                                    {plan.highlighted && <span className="text-xs font-semibold text-cafe">Mais escolhido</span>}
                                </span>
                                <span className="text-[13px] text-suave">{plan.features[0].text}</span>
                            </span>
                            <span className="shrink-0 text-right text-[15px] font-bold">
                                {plan.formattedPrice}
                                <span className="block text-xs font-normal text-suave">por mês</span>
                            </span>
                        </label>
                    ))}
                    <InputError message={errors.plan} />
                </fieldset>

                <fieldset className="flex flex-col gap-3.5">
                    <legend className={legend}>A empresa</legend>
                    <div>
                        <InputLabel htmlFor="salon_name" value="Nome da empresa" />
                        <TextInput {...input('salon_name')} required autoFocus autoComplete="organization" placeholder="Ex.: Studio Bela Vista" />
                        <InputError message={errors.salon_name} />
                    </div>
                    <div>
                        <InputLabel htmlFor="business_type" value="Tipo de negócio" />
                        <SelectInput id="business_type" value={data.business_type} onChange={(e) => setData('business_type', e.target.value)} required className="h-12">
                            <option value="" disabled>
                                Escolha uma opção
                            </option>
                            {businessTypes.map((type) => (
                                <option key={type.value} value={type.value}>
                                    {type.label}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError message={errors.business_type} />
                    </div>
                    <div className="grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="phone" value="WhatsApp da empresa" />
                            <TextInput {...input('phone')} type="tel" required autoComplete="tel" inputMode="tel" placeholder="(11) 99999-9999" />
                            <InputError message={errors.phone} />
                        </div>
                        <div>
                            <InputLabel htmlFor="city">
                                Cidade <span className="font-normal text-suave">(opcional)</span>
                            </InputLabel>
                            <TextInput {...input('city')} autoComplete="address-level2" placeholder="Ex.: Curitiba - PR" />
                            <InputError message={errors.city} />
                        </div>
                    </div>
                </fieldset>

                <fieldset className="flex flex-col gap-3.5">
                    <legend className={legend}>Seus dados de acesso</legend>
                    <div>
                        <InputLabel htmlFor="name" value="Seu nome" />
                        <TextInput {...input('name')} required autoComplete="name" />
                        <InputError message={errors.name} />
                    </div>
                    <div>
                        <InputLabel htmlFor="email" value="E-mail" />
                        <TextInput {...input('email')} type="email" required autoComplete="username" placeholder="voce@suaempresa.com.br" />
                        <InputError message={errors.email} />
                    </div>
                    <div className="grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="password" value="Senha" />
                            <TextInput {...input('password')} type="password" required autoComplete="new-password" />
                        </div>
                        <div>
                            <InputLabel htmlFor="password_confirmation" value="Confirmar senha" />
                            <TextInput {...input('password_confirmation')} type="password" required autoComplete="new-password" />
                        </div>
                    </div>
                    <InputError message={errors.password} className="!mt-0" />
                    <InputError message={errors.password_confirmation} className="!mt-0" />
                </fieldset>

                <PrimaryButton className="h-12 w-full text-[15px]" disabled={form.processing}>
                    Começar meu teste grátis
                </PrimaryButton>

                <p className="text-center text-sm text-suave">
                    Já tem cadastro?{' '}
                    <Link href={route('login')} className="font-semibold text-cafe hover:text-cafe-escuro">
                        Entrar
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
