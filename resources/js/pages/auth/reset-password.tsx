import { useForm } from '@inertiajs/react';
import { PrimaryButton } from '@/components/Buttons';
import { InputError, InputLabel, TextInput } from '@/components/Form';
import GuestLayout from '@/layouts/GuestLayout';

export default function ResetPassword({ token, email }: { token: string; email: string | null }) {
    const form = useForm({ token, email: email ?? '', password: '', password_confirmation: '' });
    const { data, setData, errors } = form;

    return (
        <GuestLayout title="Redefinir senha">
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('password.store'), { onFinish: () => form.reset('password', 'password_confirmation') });
                }}
            >
                <div>
                    <InputLabel htmlFor="email" value="E-mail" />
                    <TextInput id="email" className="h-12" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} required autoFocus autoComplete="username" />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Senha" />
                    <TextInput id="password" className="h-12" type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} required autoComplete="new-password" />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password_confirmation" value="Confirmar senha" />
                    <TextInput
                        id="password_confirmation"
                        className="h-12"
                        type="password"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                        autoComplete="new-password"
                    />
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                <div className="mt-4 flex items-center justify-end">
                    <PrimaryButton disabled={form.processing}>Redefinir senha</PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
