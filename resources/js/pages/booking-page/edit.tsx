import { router, useForm } from '@inertiajs/react';
import { useRef, useState, type ReactNode } from 'react';
import { type PageTheme, SalonLogo, ShowcaseHero, ThemeScope } from '@/components/BookingShowcase';
import { PrimaryButton } from '@/components/Buttons';
import { Field, InputError, TextareaInput, TextInput } from '@/components/Form';
import Icon from '@/components/Icon';
import { cx } from '@/lib/format';
import { shrinkImage } from '@/lib/image';
import Page from '@/layouts/Page';

type Photo = { id: number; url: string };

type Props = {
    salon: {
        name: string;
        page_headline: string | null;
        page_about: string | null;
        page_accent_color: string;
        page_background_color: string;
        phone: string | null;
        city: string | null;
        address: string | null;
        instagram: string | null;
    };
    photos: { logo: Photo | null; cover: Photo | null; gallery: Photo[] };
    galleryLimit: number;
    bookingUrl: string;
    hoursSummary: string;
};

type PhotoKind = 'logo' | 'capa' | 'galeria';

// Sugestões de cores. A dona do salão também pode escolher qualquer outra no seletor.
const accentColors = [
    { value: '#925837', label: 'Café' },
    { value: '#B4536E', label: 'Rosé' },
    { value: '#7A2E3B', label: 'Vinho' },
    { value: '#6B5B95', label: 'Lavanda' },
    { value: '#3F6B4F', label: 'Verde' },
    { value: '#2F5D7C', label: 'Azul' },
    { value: '#B8860B', label: 'Dourado' },
    { value: '#1C0D06', label: 'Preto' },
];

const backgroundColors = [
    { value: '#FEFAF3', label: 'Creme' },
    { value: '#FFFFFF', label: 'Branco' },
    { value: '#FBF0F0', label: 'Rosa claro' },
    { value: '#F4F1FA', label: 'Lavanda clara' },
    { value: '#F1F5F0', label: 'Verde claro' },
    { value: '#F1F4F8', label: 'Azul claro' },
];

/** "#925837" -> "146 88 55", igual ao App\Support\Color, para a prévia mudar na hora */
function channels(hex: string, darken = 0): string {
    return [1, 3, 5].map((start) => Math.round(parseInt(hex.slice(start, start + 2), 16) * (1 - darken))).join(' ');
}

function previewTheme(accent: string, background: string): PageTheme {
    return { accent, accentChannels: channels(accent), accentDarkChannels: channels(accent, 0.18), background };
}

/** Minha página: como a página pública de agendamento aparece para as clientes */
export default function BookingPageEdit({ salon, photos, galleryLimit, bookingUrl, hoursSummary }: Props) {
    const form = useForm({
        page_accent_color: salon.page_accent_color,
        page_background_color: salon.page_background_color,
        page_headline: salon.page_headline ?? '',
        page_about: salon.page_about ?? '',
        phone: salon.phone ?? '',
        city: salon.city ?? '',
        address: salon.address ?? '',
        instagram: salon.instagram ? `@${salon.instagram}` : '',
    });
    const { data, setData, errors } = form;
    const theme = previewTheme(data.page_accent_color, data.page_background_color);

    return (
        <Page
            title="Minha página"
            subtitle="A página em que as clientes conhecem o salão e agendam sozinhas. Escolha as cores, as fotos e os textos."
            actions={
                <a
                    href={bookingUrl}
                    target="_blank"
                    rel="noopener"
                    className="botao-vidro inline-flex h-11 items-center justify-center gap-2 rounded-campo border border-linha bg-cartao px-5 text-sm font-semibold text-tinta hover:bg-areia"
                >
                    <Icon name="link" className="h-[18px] w-[18px]" />
                    Ver minha página
                </a>
            }
        >
            <div className="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
                <div className="flex min-w-0 flex-col gap-5">
                    <Section title="Fotos" description="As fotos são salvas assim que você escolhe. A capa fica no topo da página e o logo aparece em todas as telas.">
                        <div className="grid gap-5 sm:grid-cols-[auto_1fr]">
                            <PhotoSlot kind="logo" label="Logo" photo={photos.logo} round />
                            <PhotoSlot kind="capa" label="Foto de capa" photo={photos.cover} />
                        </div>
                        <Gallery photos={photos.gallery} limit={galleryLimit} />
                    </Section>

                    <form
                        className="flex flex-col gap-5"
                        onSubmit={(event) => {
                            event.preventDefault();
                            form.patch(route('booking-page.update'), { preserveScroll: true });
                        }}
                    >
                        <Section title="Cores" description="A cor de destaque vai nos botões e detalhes. O fundo é a cor da página inteira.">
                            <ColorPicker
                                name="page_accent_color"
                                label="Cor de destaque"
                                options={accentColors}
                                value={data.page_accent_color}
                                onChange={(value) => setData('page_accent_color', value)}
                                error={errors.page_accent_color}
                            />
                            <ColorPicker
                                name="page_background_color"
                                label="Cor do fundo"
                                options={backgroundColors}
                                value={data.page_background_color}
                                onChange={(value) => setData('page_background_color', value)}
                                error={errors.page_background_color}
                            />
                        </Section>

                        <Section title="Textos" description="Conte em poucas palavras o que o salão tem de especial.">
                            <Field id="page_headline" label="Frase de destaque" error={errors.page_headline}>
                                <TextInput
                                    id="page_headline"
                                    maxLength={120}
                                    value={data.page_headline}
                                    onChange={(e) => setData('page_headline', e.target.value)}
                                    placeholder="Ex.: Cabelo, unhas e autoestima no coração da cidade"
                                />
                            </Field>
                            <Field id="page_about" label="Sobre o salão" error={errors.page_about}>
                                <TextareaInput
                                    id="page_about"
                                    rows={5}
                                    maxLength={1000}
                                    value={data.page_about}
                                    onChange={(e) => setData('page_about', e.target.value)}
                                    placeholder="Há quanto tempo vocês atendem, especialidades, estrutura, estacionamento..."
                                />
                            </Field>
                        </Section>

                        <Section title="Contatos" description="Aparecem no fim da página, com links para abrir o mapa, o Instagram e o WhatsApp.">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <Field id="phone" label="WhatsApp" error={errors.phone}>
                                    <TextInput id="phone" type="tel" value={data.phone} onChange={(e) => setData('phone', e.target.value)} placeholder="(11) 98765-4321" />
                                </Field>
                                <Field id="instagram" label="Instagram" error={errors.instagram}>
                                    <TextInput id="instagram" value={data.instagram} onChange={(e) => setData('instagram', e.target.value)} placeholder="@seusalao" />
                                </Field>
                                <Field id="address" label="Endereço" error={errors.address}>
                                    <TextInput id="address" value={data.address} onChange={(e) => setData('address', e.target.value)} placeholder="Rua das Flores, 123 - Centro" />
                                </Field>
                                <Field id="city" label="Cidade" error={errors.city}>
                                    <TextInput id="city" value={data.city} onChange={(e) => setData('city', e.target.value)} placeholder="São Paulo - SP" />
                                </Field>
                            </div>
                            <p className="text-sm text-suave">Horário de funcionamento: {hoursSummary}</p>
                        </Section>

                        <div className="flex items-center gap-3">
                            <PrimaryButton disabled={form.processing}>Salvar página</PrimaryButton>
                            {form.isDirty && <span className="text-sm text-suave">Há mudanças sem salvar.</span>}
                        </div>
                    </form>
                </div>

                {/* Prévia: muda na hora, antes mesmo de salvar */}
                <aside className="flex flex-col gap-2 lg:sticky lg:top-4">
                    <span className="text-[13px] font-semibold uppercase tracking-wider text-cafe">Prévia</span>
                    <ThemeScope theme={theme} className="flex flex-col gap-4 overflow-hidden rounded-[30px] border-[6px] border-tinta p-4 shadow-menu">
                        <div className="vidro flex items-center justify-center gap-2 rounded-full p-2">
                            {photos.logo && <SalonLogo name={salon.name} url={photos.logo.url} className="h-7 w-7" />}
                            <span className="truncate font-display text-lg italic">{salon.name}</span>
                        </div>
                        <ShowcaseHero name={salon.name} city={data.city || null} logoUrl={photos.logo?.url ?? null} showcase={{ coverUrl: photos.cover?.url ?? null, headline: data.page_headline || null }} />
                        <div className="flex items-center gap-3 rounded-[18px] border border-cafe bg-cartao px-4 py-3 ring-1 ring-cafe">
                            <span className="flex flex-1 flex-col">
                                <span className="text-sm font-bold">Escova</span>
                                <span className="text-xs text-suave">60 min · R$ 60,00</span>
                            </span>
                            <span className="flex h-6 w-6 items-center justify-center rounded-full bg-cafe text-white">
                                <Icon name="check" className="h-3.5 w-3.5" />
                            </span>
                        </div>
                        <span className="botao-vidro inline-flex h-11 items-center justify-center rounded-full bg-cafe text-sm font-semibold text-white">Agendar horário</span>
                    </ThemeScope>
                </aside>
            </div>
        </Page>
    );
}

function Section({ title, description, children }: { title: string; description: string; children: ReactNode }) {
    return (
        <section className="cartao flex flex-col gap-5 p-5 sm:p-6">
            <header>
                <h2 className="font-sans text-lg font-bold">{title}</h2>
                <p className="mt-1 text-sm text-suave">{description}</p>
            </header>
            {children}
        </section>
    );
}

function ColorPicker({
    name,
    label,
    options,
    value,
    onChange,
    error,
}: {
    name: string;
    label: string;
    options: { value: string; label: string }[];
    value: string;
    onChange: (value: string) => void;
    error?: string;
}) {
    const custom = !options.some((option) => option.value.toLowerCase() === value.toLowerCase());

    return (
        <fieldset>
            <legend className="mb-2 text-sm font-semibold">{label}</legend>
            <div className="flex flex-wrap items-center gap-2.5">
                {options.map((option) => (
                    <label key={option.value} title={option.label} className="cursor-pointer">
                        <input type="radio" name={name} value={option.value} checked={option.value.toLowerCase() === value.toLowerCase()} onChange={() => onChange(option.value)} className="peer sr-only" />
                        <span className="sr-only">{option.label}</span>
                        <span
                            className="flex h-11 w-11 items-center justify-center rounded-full border border-linha-forte shadow-cartao ring-offset-2 peer-checked:ring-2 peer-checked:ring-tinta peer-focus-visible:ring-2 peer-focus-visible:ring-caramelo"
                            style={{ backgroundColor: option.value }}
                        />
                    </label>
                ))}
                {/* Qualquer outra cor */}
                <label
                    className={cx(
                        'relative flex h-11 cursor-pointer items-center gap-2 rounded-full border border-linha-forte bg-cartao pl-1.5 pr-4 text-sm font-semibold ring-offset-2 focus-within:ring-2 focus-within:ring-caramelo',
                        custom && 'ring-2 ring-tinta',
                    )}
                >
                    <span className="h-8 w-8 rounded-full border border-linha-forte" style={{ backgroundColor: value }} />
                    Outra cor
                    <input type="color" value={value} onChange={(e) => onChange(e.target.value.toUpperCase())} className="absolute inset-0 h-full w-full cursor-pointer opacity-0" />
                </label>
            </div>
            <InputError message={error} />
        </fieldset>
    );
}

/** Envia a foto já reduzida. Logo e capa trocam a anterior. */
function useUpload(kind: PhotoKind) {
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const upload = async (file: File | undefined) => {
        if (!file) {
            return;
        }

        setUploading(true);
        setError(null);
        const photo = await shrinkImage(file, kind === 'logo' ? 600 : 1600);

        router.post(
            route('booking-page.photos.store'),
            { kind, photo },
            {
                preserveScroll: true,
                preserveState: true,
                forceFormData: true,
                onError: (errors) => setError(errors.photo ?? errors.kind ?? 'Não foi possível salvar a foto.'),
                onFinish: () => setUploading(false),
            },
        );
    };

    return { uploading, error, upload };
}

function removePhoto(photo: Photo, message: string) {
    if (window.confirm(message)) {
        router.delete(route('booking-page.photos.destroy', photo.id), { preserveScroll: true, preserveState: true });
    }
}

function PhotoSlot({ kind, label, photo, round }: { kind: PhotoKind; label: string; photo: Photo | null; round?: boolean }) {
    const input = useRef<HTMLInputElement>(null);
    const { uploading, error, upload } = useUpload(kind);

    return (
        <div className="flex min-w-0 flex-col gap-2">
            <span className="text-sm font-semibold">{label}</span>
            <button
                type="button"
                onClick={() => input.current?.click()}
                disabled={uploading}
                className={cx(
                    'group relative flex items-center justify-center overflow-hidden border border-dashed border-linha-forte bg-areia text-suave transition hover:border-cafe hover:text-cafe',
                    round ? 'h-32 w-32 rounded-full' : 'aspect-[16/9] w-full rounded-[18px]',
                )}
            >
                {photo ? <img src={photo.url} alt={label} className="h-full w-full object-cover" /> : <Icon name="foto" className="h-8 w-8" />}
                <span className="absolute inset-x-0 bottom-0 bg-tinta/60 py-1.5 text-xs font-semibold text-white opacity-90">{uploading ? 'Enviando...' : photo ? 'Trocar' : 'Escolher foto'}</span>
            </button>
            <input ref={input} type="file" accept="image/jpeg,image/png,image/webp" className="sr-only" onChange={(e) => upload(e.target.files?.[0]).then(() => (e.target.value = ''))} />
            {photo && (
                <button type="button" onClick={() => removePhoto(photo, `Apagar ${label.toLowerCase()}?`)} className="self-start text-sm font-semibold text-perigo hover:underline">
                    Apagar
                </button>
            )}
            <InputError message={error} />
        </div>
    );
}

function Gallery({ photos, limit }: { photos: Photo[]; limit: number }) {
    const input = useRef<HTMLInputElement>(null);
    const { uploading, error, upload } = useUpload('galeria');

    return (
        <div className="flex flex-col gap-2">
            <span className="text-sm font-semibold">
                Galeria <span className="font-normal text-suave">· {photos.length} de {limit} fotos</span>
            </span>
            <div className="grid grid-cols-3 gap-2.5 sm:grid-cols-4">
                {photos.map((photo, index) => (
                    <div key={photo.id} className="relative aspect-square overflow-hidden rounded-[14px] bg-areia">
                        <img src={photo.url} alt={`Foto ${index + 1} da galeria`} className="h-full w-full object-cover" />
                        <button
                            type="button"
                            onClick={() => removePhoto(photo, 'Apagar esta foto da galeria?')}
                            className="botao-vidro absolute right-1.5 top-1.5 flex h-9 w-9 items-center justify-center rounded-full bg-cartao/90 text-perigo hover:bg-white"
                        >
                            <span className="sr-only">Apagar foto {index + 1}</span>
                            <Icon name="lixo" className="h-4 w-4" />
                        </button>
                    </div>
                ))}
                {photos.length < limit && (
                    <button
                        type="button"
                        onClick={() => input.current?.click()}
                        disabled={uploading}
                        className="flex aspect-square flex-col items-center justify-center gap-1 rounded-[14px] border border-dashed border-linha-forte bg-areia text-xs font-semibold text-suave transition hover:border-cafe hover:text-cafe"
                    >
                        <Icon name="mais" className="h-6 w-6" />
                        {uploading ? 'Enviando...' : 'Adicionar'}
                    </button>
                )}
            </div>
            <input ref={input} type="file" accept="image/jpeg,image/png,image/webp" className="sr-only" onChange={(e) => upload(e.target.files?.[0]).then(() => (e.target.value = ''))} />
            <InputError message={error} />
        </div>
    );
}
