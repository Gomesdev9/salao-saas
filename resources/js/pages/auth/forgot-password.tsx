import { useForm, usePage } from '@inertiajs/react';
import { PrimaryButton } from '@/components/Buttons';
import { InputError, InputLabel, TextInput } from '@/components/Form';
import GuestLayout, { SessionStatus } from '@/layouts/GuestLayout';

export default function ForgotPassword() {
    const { status } = usePage().props.flash;
    const form = useForm({ email: '' });

    return (
        <GuestLayout title="Esqueceu a senha?">
            <div className="mb-4 text-sm text-suave">Esqueceu a senha? Sem problema. Informe seu e-mail e enviaremos um link para você criar uma nova.</div>

            <SessionStatus status={status} className="mb-4" />

            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('password.email'));
                }}
            >
                <div>
                    <InputLabel htmlFor="email" value="E-mail" />
                    <TextInput id="email" className="h-12" type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required autoFocus />
                    <InputError message={form.errors.email} className="mt-2" />
                </div>

                <div className="mt-4 flex items-center justify-end">
                    <PrimaryButton disabled={form.processing}>Enviar link de redefinição de senha</PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
