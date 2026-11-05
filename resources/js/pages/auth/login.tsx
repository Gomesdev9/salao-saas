import { Link, useForm, usePage } from '@inertiajs/react';
import { PrimaryButton } from '@/components/Buttons';
import { Checkbox, InputError, InputLabel, TextInput } from '@/components/Form';
import Icon from '@/components/Icon';
import GuestLayout, { SessionStatus } from '@/layouts/GuestLayout';

const features = [
    'Agenda do dia com uma coluna por profissional',
    'Clientes com WhatsApp, aniversário e observações',
    'Serviços com duração e preço, comissão por profissional',
];

export default function Login({ canResetPassword }: { canResetPassword: boolean }) {
    const { status } = usePage().props.flash;
    const form = useForm({ email: '', password: '', remember: false });
    const { data, setData, errors } = form;

    return (
        <GuestLayout
            title="Entrar"
            aside={
                <>
                    <h1 className="text-4xl font-bold leading-tight sm:text-[40px]">
                        A agenda do seu salão, <em className="italic text-[#D9A57F]">organizada</em>.
                    </h1>
                    <ul className="flex flex-col gap-3.5 text-base text-menu-texto">
                        {features.map((feature) => (
                            <li key={feature} className="flex items-start gap-3">
                                <Icon name="check" className="mt-0.5 h-5 w-5 text-[#D9A57F]" />
                                {feature}
                            </li>
                        ))}
                    </ul>
                </>
            }
        >
            <form
                className="flex flex-col gap-5"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('login'), { onFinish: () => form.reset('password') });
                }}
            >
                <div className="flex flex-col gap-1.5">
                    <h2 className="text-[32px] font-semibold leading-tight">Entrar</h2>
                    <span className="text-[15px] text-suave">Use o e-mail cadastrado da empresa.</span>
                </div>

                <SessionStatus status={status} />

                <div>
                    <InputLabel htmlFor="email" value="E-mail" />
                    <TextInput
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        autoFocus
                        autoComplete="username"
                        placeholder="voce@seusalao.com.br"
                        className="h-12"
                    />
                    <InputError message={errors.email} />
                </div>

                <div>
                    <div className="mb-1.5 flex items-center justify-between">
                        <InputLabel htmlFor="password" value="Senha" className="!mb-0" />
                        {canResetPassword && (
                            <Link href={route('password.request')} className="text-sm font-semibold text-cafe hover:text-cafe-escuro">
                                Esqueceu a senha?
                            </Link>
                        )}
                    </div>
                    <TextInput id="password" type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} required autoComplete="current-password" className="h-12" />
                    <InputError message={errors.password} />
                </div>

                <Checkbox id="remember_me" label="Lembrar de mim" checked={data.remember} onChange={(e) => setData('remember', e.target.checked)} />

                <PrimaryButton className="h-12 w-full text-[15px]" disabled={form.processing}>
                    Entrar
                </PrimaryButton>

                <p className="text-center text-sm text-suave">
                    Ainda não tem conta?{' '}
                    <Link href={route('register')} className="font-semibold text-cafe hover:text-cafe-escuro">
                        Cadastre sua empresa
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
