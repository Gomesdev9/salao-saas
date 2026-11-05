import { Link, useForm, usePage } from '@inertiajs/react';
import { PrimaryButton } from '@/components/Buttons';
import GuestLayout from '@/layouts/GuestLayout';

export default function VerifyEmail() {
    const { status } = usePage().props.flash;
    const form = useForm({});

    return (
        <GuestLayout title="Confirme seu e-mail">
            <div className="mb-4 text-sm text-suave">
                Obrigado pelo cadastro! Antes de começar, confirme seu e-mail clicando no link que acabamos de enviar. Se não recebeu, podemos enviar outro.
            </div>

            {status === 'verification-link-sent' && <div className="mb-4 text-sm font-medium text-[#1C5234]">Um novo link de verificação foi enviado para o e-mail informado no cadastro.</div>}

            <div className="mt-4 flex items-center justify-between">
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.post(route('verification.send'));
                    }}
                >
                    <PrimaryButton disabled={form.processing}>Reenviar e-mail de verificação</PrimaryButton>
                </form>

                <Link href={route('logout')} method="post" as="button" className="text-sm font-semibold text-cafe underline hover:text-cafe-escuro">
                    Sair
                </Link>
            </div>
        </GuestLayout>
    );
}
