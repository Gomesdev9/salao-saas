import { useForm } from '@inertiajs/react';
import { PrimaryButton } from '@/components/Buttons';
import { InputError, InputLabel, TextInput } from '@/components/Form';
import GuestLayout from '@/layouts/GuestLayout';

export default function ConfirmPassword() {
    const form = useForm({ password: '' });

    return (
        <GuestLayout title="Confirmar senha">
            <div className="mb-4 text-sm text-suave">Esta é uma área protegida. Confirme sua senha para continuar.</div>

            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('password.confirm'), { onFinish: () => form.reset('password') });
                }}
            >
                <div>
                    <InputLabel htmlFor="password" value="Senha" />
                    <TextInput id="password" className="h-12" type="password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} required autoComplete="current-password" />
                    <InputError message={form.errors.password} className="mt-2" />
                </div>

                <div className="mt-4 flex justify-end">
                    <PrimaryButton disabled={form.processing}>Confirmar</PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
