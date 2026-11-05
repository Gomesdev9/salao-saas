import { Link, useForm } from '@inertiajs/react';
import FormFooter from '@/components/FormFooter';
import { Checkbox, Field, InputError, TextInput } from '@/components/Form';
import RemoveButton from '@/components/RemoveButton';
import Page from '@/layouts/Page';

type Props = {
    professional: { id: number; name: string; phone: string | null; commission_percent: number; active: boolean; services: number[] } | null;
    services: { id: number; name: string }[];
    canActivate: boolean;
    /** Aviso quando a equipe ativa já chegou no limite do plano */
    planLimit: string | null;
};

/** Novo profissional e editar profissional */
export default function ProfessionalForm({ professional, services, canActivate, planLimit }: Props) {
    const form = useForm({
        name: professional?.name ?? '',
        phone: professional?.phone ?? '',
        commission_percent: String(professional?.commission_percent ?? 0),
        services: professional?.services ?? [],
        // No limite do plano, o formulário já vem com "inativo" marcado
        active: professional?.active ?? canActivate,
    });
    const { data, setData, errors } = form;
    const serviceErrors = Object.entries(errors)
        .filter(([key]) => key.startsWith('services.'))
        .map(([, message]) => message);

    const toggleService = (id: number, checked: boolean) =>
        setData('services', checked ? [...data.services, id] : data.services.filter((item) => item !== id));

    return (
        <Page title={professional ? 'Editar profissional' : 'Novo profissional'} back={route('professionals.index')} backLabel="Voltar para profissionais">
            {planLimit && (
                <div role="status" className="max-w-3xl rounded-campo bg-mel px-4 py-3 text-sm leading-relaxed text-mel-texto">
                    {planLimit} Você pode cadastrar como inativo ou{' '}
                    <Link href={route('plan.edit')} className="font-semibold underline">
                        mudar de plano
                    </Link>
                    .
                </div>
            )}

            <form
                className="cartao max-w-3xl"
                onSubmit={(event) => {
                    event.preventDefault();
                    professional ? form.put(route('professionals.update', professional.id)) : form.post(route('professionals.store'));
                }}
            >
                <div className="flex flex-col gap-[18px] p-6">
                    <Field id="name" label="Nome" error={errors.name}>
                        <TextInput id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} required autoFocus />
                    </Field>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field id="phone" label="Telefone / WhatsApp" error={errors.phone}>
                            <TextInput id="phone" type="tel" value={data.phone} onChange={(e) => setData('phone', e.target.value)} placeholder="(00) 90000-0000" />
                        </Field>
                        <Field id="commission_percent" label="Comissão (%)" error={errors.commission_percent}>
                            <TextInput
                                id="commission_percent"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                value={data.commission_percent}
                                onChange={(e) => setData('commission_percent', e.target.value)}
                                required
                            />
                        </Field>
                    </div>

                    <fieldset>
                        <legend className="mb-1 text-sm font-semibold">Serviços que faz</legend>
                        <div className="grid gap-x-4 sm:grid-cols-2">
                            {services.length > 0 ? (
                                services.map((service) => (
                                    <Checkbox
                                        key={service.id}
                                        label={service.name}
                                        checked={data.services.includes(service.id)}
                                        onChange={(e) => toggleService(service.id, e.target.checked)}
                                    />
                                ))
                            ) : (
                                <p className="text-sm text-suave">Cadastre os serviços primeiro para poder vinculá-los.</p>
                            )}
                        </div>
                        <InputError message={serviceErrors} />
                    </fieldset>

                    <div>
                        <Checkbox label="Ativo (aparece na agenda)" checked={data.active} onChange={(e) => setData('active', e.target.checked)} />
                        <InputError message={errors.active} />
                        {errors.active && (
                            <Link href={route('plan.edit')} className="mt-1 inline-flex min-h-11 items-center text-sm font-semibold text-cafe underline">
                                Ver os planos
                            </Link>
                        )}
                    </div>
                </div>

                <FormFooter
                    cancel={route('professionals.index')}
                    processing={form.processing}
                    remove={
                        professional && (
                            <RemoveButton
                                href={route('professionals.destroy', professional.id)}
                                confirm="Remover este profissional? Os agendamentos dele também serão apagados. Se ele só parou de atender, prefira desmarcar “Ativo”."
                            >
                                Remover profissional
                            </RemoveButton>
                        )
                    }
                />
            </form>
        </Page>
    );
}
