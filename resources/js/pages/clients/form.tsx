import { useForm } from '@inertiajs/react';
import FormFooter from '@/components/FormFooter';
import { Field, TextareaInput, TextInput } from '@/components/Form';
import RemoveButton from '@/components/RemoveButton';
import Page from '@/layouts/Page';

type Props = {
    client: { id: number; name: string; whatsapp: string | null; birthday: string | null; notes: string | null } | null;
};

/** Novo cliente e editar cliente */
export default function ClientForm({ client }: Props) {
    const form = useForm({
        name: client?.name ?? '',
        whatsapp: client?.whatsapp ?? '',
        birthday: client?.birthday ?? '',
        notes: client?.notes ?? '',
    });
    const { data, setData, errors } = form;

    return (
        <Page title={client ? 'Editar cliente' : 'Novo cliente'} back={route('clients.index')} backLabel="Voltar para clientes">
            <form
                className="cartao max-w-3xl"
                onSubmit={(event) => {
                    event.preventDefault();
                    client ? form.put(route('clients.update', client.id)) : form.post(route('clients.store'));
                }}
            >
                <div className="flex flex-col gap-[18px] p-6">
                    <Field id="name" label="Nome" error={errors.name}>
                        <TextInput id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} required autoFocus />
                    </Field>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field id="whatsapp" label="WhatsApp" error={errors.whatsapp}>
                            <TextInput id="whatsapp" type="tel" value={data.whatsapp} onChange={(e) => setData('whatsapp', e.target.value)} placeholder="(00) 90000-0000" />
                        </Field>
                        <Field id="birthday" label="Data de nascimento" error={errors.birthday}>
                            <TextInput id="birthday" type="date" value={data.birthday} onChange={(e) => setData('birthday', e.target.value)} />
                        </Field>
                    </div>

                    <Field id="notes" label="Observações (alergias, preferências...)" error={errors.notes}>
                        <TextareaInput id="notes" rows={3} value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
                    </Field>
                </div>

                <FormFooter
                    cancel={route('clients.index')}
                    processing={form.processing}
                    remove={
                        client && (
                            <RemoveButton href={route('clients.destroy', client.id)} confirm="Remover este cliente? Os agendamentos dele também serão apagados.">
                                Remover cliente
                            </RemoveButton>
                        )
                    }
                />
            </form>
        </Page>
    );
}
