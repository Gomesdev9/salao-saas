import { useForm } from '@inertiajs/react';
import FormFooter from '@/components/FormFooter';
import { Checkbox, Field, TextInput } from '@/components/Form';
import RemoveButton from '@/components/RemoveButton';
import Page from '@/layouts/Page';

type Props = {
    service: { id: number; name: string; category: string | null; duration_minutes: number; price: string; active: boolean } | null;
    /** Categorias já usadas no salão, para sugerir no campo */
    categories: string[];
};

/** Novo serviço e editar serviço */
export default function ServiceForm({ service, categories }: Props) {
    const form = useForm({
        name: service?.name ?? '',
        category: service?.category ?? '',
        duration_minutes: String(service?.duration_minutes ?? 30),
        price: service?.price ?? '',
        active: service?.active ?? true,
    });
    const { data, setData, errors } = form;

    return (
        <Page title={service ? 'Editar serviço' : 'Novo serviço'} back={route('services.index')} backLabel="Voltar para serviços">
            <form
                className="cartao max-w-3xl"
                onSubmit={(event) => {
                    event.preventDefault();
                    service ? form.put(route('services.update', service.id)) : form.post(route('services.store'));
                }}
            >
                <div className="flex flex-col gap-[18px] p-6">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field id="name" label="Nome do serviço" error={errors.name}>
                            <TextInput id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} required autoFocus />
                        </Field>
                        <Field id="category" label="Categoria" error={errors.category}>
                            <TextInput id="category" list="categorias" value={data.category} onChange={(e) => setData('category', e.target.value)} placeholder="Cabelo, Unhas, Estética..." />
                            <datalist id="categorias">
                                {categories.map((category) => (
                                    <option key={category} value={category} />
                                ))}
                            </datalist>
                        </Field>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field id="duration_minutes" label="Duração (minutos)" error={errors.duration_minutes}>
                            <TextInput
                                id="duration_minutes"
                                type="number"
                                min="5"
                                max="600"
                                step="5"
                                value={data.duration_minutes}
                                onChange={(e) => setData('duration_minutes', e.target.value)}
                                required
                            />
                        </Field>
                        <Field id="price" label="Preço (R$)" error={errors.price}>
                            <TextInput id="price" type="number" min="0" step="0.01" value={data.price} onChange={(e) => setData('price', e.target.value)} required />
                        </Field>
                    </div>

                    <div>
                        <Checkbox label="Ativo (disponível para agendar)" checked={data.active} onChange={(e) => setData('active', e.target.checked)} />
                    </div>
                </div>

                <FormFooter
                    cancel={route('services.index')}
                    processing={form.processing}
                    remove={
                        service && (
                            <RemoveButton
                                href={route('services.destroy', service.id)}
                                confirm="Remover este serviço? Os agendamentos com ele também serão apagados. Se só parou de oferecer, prefira desmarcar “Ativo”."
                            >
                                Remover serviço
                            </RemoveButton>
                        )
                    }
                />
            </form>
        </Page>
    );
}
