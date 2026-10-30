import { Link, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState, type ReactNode } from 'react';
import { DangerButton, PrimaryButton, SecondaryButton } from '@/components/Buttons';
import { InputError, InputLabel, TextInput } from '@/components/Form';
import Modal from '@/components/Modal';
import Page from '@/layouts/Page';

type Props = {
    mustVerifyEmail: boolean;
    salon: { name: string; pix_key: string | null };
};

/** Meu perfil: dados da pessoa, dados da empresa, senha e excluir conta */
export default function ProfileEdit({ mustVerifyEmail, salon }: Props) {
    return (
        <Page title="Perfil">
            <div className="flex max-w-3xl flex-col gap-5">
                <div className="cartao p-5 sm:p-8">
                    <ProfileInformation mustVerifyEmail={mustVerifyEmail} />
                </div>
                <div id="empresa" className="cartao p-5 sm:p-8">
                    <SalonForm salon={salon} />
                </div>
                <div className="cartao p-5 sm:p-8">
                    <PasswordForm />
                </div>
                <div className="cartao p-5 sm:p-8">
                    <DeleteAccount />
                </div>
            </div>
        </Page>
    );
}

function Header({ title, children }: { title: string; children: ReactNode }) {
    return (
        <header>
            <h2 className="text-xl font-semibold">{title}</h2>
            <p className="mt-1 text-sm text-suave">{children}</p>
        </header>
    );
}

/** "Salvo." ao lado do botão, some depois de 2 segundos */
function Saved({ when }: { when: boolean }) {
    const [show, setShow] = useState(when);

    useEffect(() => {
        setShow(when);
        const timer = setTimeout(() => setShow(false), 2000);

        return () => clearTimeout(timer);
    }, [when]);

    return show ? <p className="text-sm text-suave">Salvo.</p> : null;
}

function ProfileInformation({ mustVerifyEmail }: { mustVerifyEmail: boolean }) {
    const { auth, flash } = usePage().props;
    const form = useForm({ name: auth!.user.name, email: auth!.user.email });
    const { data, setData, errors } = form;

    return (
        <section>
            <Header title="Dados do perfil">Atualize seu nome e e-mail.</Header>

            <form
                className="mt-6 space-y-6"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.patch(route('profile.update'), { preserveScroll: true });
                }}
            >
                <div>
                    <InputLabel htmlFor="name" value="Seu nome" />
                    <TextInput id="name" className="mt-1" value={data.name} onChange={(e) => setData('name', e.target.value)} required autoFocus autoComplete="name" />
                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="E-mail" />
                    <TextInput id="email" type="email" className="mt-1" value={data.email} onChange={(e) => setData('email', e.target.value)} required autoComplete="username" />
                    <InputError className="mt-2" message={errors.email} />

                    {mustVerifyEmail && (
                        <div>
                            <p className="mt-2 text-sm text-tinta">
                                Seu e-mail ainda não foi verificado.{' '}
                                <Link href={route('verification.send')} method="post" as="button" className="text-sm font-semibold text-cafe underline hover:text-cafe-escuro">
                                    Clique aqui para reenviar o e-mail de verificação.
                                </Link>
                            </p>
                            {flash.status === 'verification-link-sent' && (
                                <p className="mt-2 text-sm font-medium text-[#1C5234]">Um novo link de verificação foi enviado para o seu e-mail.</p>
                            )}
                        </div>
                    )}
                </div>

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={form.processing}>Salvar</PrimaryButton>
                    <Saved when={form.recentlySuccessful} />
                </div>
            </form>
        </section>
    );
}

function SalonForm({ salon }: Pick<Props, 'salon'>) {
    const form = useForm({ salon_name: salon.name, pix_key: salon.pix_key ?? '' });
    const { data, setData, errors } = form;

    return (
        <section>
            <Header title="Dados da empresa">A chave Pix aparece na tela de receber pagamento, pronta para copiar.</Header>

            <form
                className="mt-6 space-y-6"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.patch(route('profile.salon'), { errorBag: 'salon', preserveScroll: true });
                }}
            >
                <div>
                    <InputLabel htmlFor="salon_name" value="Nome da empresa" />
                    <TextInput id="salon_name" className="mt-1" value={data.salon_name} onChange={(e) => setData('salon_name', e.target.value)} required />
                    <InputError className="mt-2" message={errors.salon_name} />
                </div>

                <div>
                    <InputLabel htmlFor="pix_key" value="Chave Pix" />
                    <TextInput
                        id="pix_key"
                        className="mt-1"
                        value={data.pix_key}
                        onChange={(e) => setData('pix_key', e.target.value)}
                        placeholder="CNPJ, e-mail, telefone ou chave aleatória"
                    />
                    <InputError className="mt-2" message={errors.pix_key} />
                </div>

                <PrimaryButton disabled={form.processing}>Salvar</PrimaryButton>
            </form>
        </section>
    );
}

function PasswordForm() {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });
    const { data, setData, errors } = form;

    return (
        <section>
            <Header title="Alterar senha">Use uma senha longa e difícil de adivinhar para manter sua conta segura.</Header>

            <form
                className="mt-6 space-y-6"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.put(route('password.update'), { errorBag: 'updatePassword', preserveScroll: true, onFinish: () => form.reset() });
                }}
            >
                <div>
                    <InputLabel htmlFor="update_password_current_password" value="Senha atual" />
                    <TextInput
                        id="update_password_current_password"
                        type="password"
                        className="mt-1"
                        value={data.current_password}
                        onChange={(e) => setData('current_password', e.target.value)}
                        autoComplete="current-password"
                    />
                    <InputError message={errors.current_password} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="update_password_password" value="Nova senha" />
                    <TextInput id="update_password_password" type="password" className="mt-1" value={data.password} onChange={(e) => setData('password', e.target.value)} autoComplete="new-password" />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="update_password_password_confirmation" value="Confirmar senha" />
                    <TextInput
                        id="update_password_password_confirmation"
                        type="password"
                        className="mt-1"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        autoComplete="new-password"
                    />
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={form.processing}>Salvar</PrimaryButton>
                    <Saved when={form.recentlySuccessful} />
                </div>
            </form>
        </section>
    );
}

function DeleteAccount() {
    const [open, setOpen] = useState(false);
    const form = useForm({ password: '' });
    const close = () => {
        setOpen(false);
        form.clearErrors();
        form.reset();
    };

    return (
        <section className="space-y-6">
            <Header title="Excluir conta">
                Ao excluir sua conta, todos os dados serão apagados permanentemente. Antes de excluir, baixe qualquer informação que queira guardar.
            </Header>

            <DangerButton type="button" onClick={() => setOpen(true)}>
                Excluir conta
            </DangerButton>

            <Modal show={open} onClose={close}>
                <form
                    className="p-6"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.delete(route('profile.destroy'), { errorBag: 'userDeletion', preserveScroll: true, onFinish: () => form.reset() });
                    }}
                >
                    <h2 className="text-xl font-semibold">Tem certeza que deseja excluir sua conta?</h2>
                    <p className="mt-1 text-sm text-suave">Ao excluir sua conta, todos os dados serão apagados permanentemente. Digite sua senha para confirmar.</p>

                    <div className="mt-6">
                        <InputLabel htmlFor="password" value="Senha" className="sr-only" />
                        <TextInput
                            id="password"
                            type="password"
                            className="mt-1 sm:w-3/4"
                            placeholder="Senha"
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                            autoFocus
                        />
                        <InputError message={form.errors.password} className="mt-2" />
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={close}>Cancelar</SecondaryButton>
                        <DangerButton className="ms-3" disabled={form.processing}>
                            Excluir conta
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </section>
    );
}
