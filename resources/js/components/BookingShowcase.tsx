import type { CSSProperties, ReactNode } from 'react';
import Icon from '@/components/Icon';

/** Cores da página pública (Salon::pageTheme) */
export type PageTheme = { accent: string; accentChannels: string; accentDarkChannels: string; background: string };

/** Vitrine do salão no começo da página pública (BookingController::showcaseProps) */
export type Showcase = {
    headline: string | null;
    about: string | null;
    coverUrl: string | null;
    gallery: string[];
    address: string | null;
    mapsUrl: string | null;
    instagram: string | null;
    instagramUrl: string | null;
    hoursSummary: string;
};

/** Aplica as cores do salão a tudo o que estiver dentro: botões (bg-cafe, text-cafe...) e o fundo */
export function ThemeScope({ theme, className, children }: { theme: PageTheme; className?: string; children: ReactNode }) {
    const style = {
        '--cor-destaque': theme.accentChannels,
        '--cor-destaque-escuro': theme.accentDarkChannels,
        backgroundColor: theme.background,
    } as CSSProperties;

    return (
        <div style={style} className={className}>
            {children}
        </div>
    );
}

/** Logo redondo do salão, ou a inicial do nome quando não há logo */
export function SalonLogo({ name, url, className }: { name: string; url: string | null; className: string }) {
    return url ? (
        <img src={url} alt={`Logo de ${name}`} className={`${className} shrink-0 rounded-full bg-cartao object-cover`} />
    ) : (
        <span className={`${className} flex shrink-0 items-center justify-center rounded-full bg-cafe font-display italic text-white`} aria-hidden="true">
            {name.charAt(0)}
        </span>
    );
}

/** Topo da vitrine: capa, logo, nome, cidade e frase de destaque */
export function ShowcaseHero({ name, city, logoUrl, showcase }: { name: string; city: string | null; logoUrl: string | null; showcase: Pick<Showcase, 'coverUrl' | 'headline'> }) {
    return (
        <section className="flex flex-col items-center gap-3 text-center">
            <div className="relative w-full">
                {showcase.coverUrl ? (
                    <img src={showcase.coverUrl} alt="" className="aspect-[16/9] w-full rounded-cartao object-cover shadow-cartao" />
                ) : (
                    <div className="aspect-[16/7] w-full rounded-cartao bg-gradient-to-br from-cafe/25 via-cafe/10 to-transparent" />
                )}
                <div className="absolute inset-x-0 -bottom-9 flex justify-center">
                    <span className="rounded-full bg-cartao p-1 shadow-cartao">
                        <SalonLogo name={name} url={logoUrl} className="h-[72px] w-[72px] text-3xl" />
                    </span>
                </div>
            </div>
            <div className="mt-9 flex flex-col gap-1">
                <h2 className="text-[26px] font-medium leading-tight">{name}</h2>
                {city && <span className="text-sm text-suave">{city}</span>}
                {showcase.headline && <p className="mt-1 text-[15px] leading-relaxed text-tinta">{showcase.headline}</p>}
            </div>
        </section>
    );
}

/** Parte de baixo da vitrine: sobre, fotos e contatos */
export function ShowcaseDetails({ name, whatsappUrl, showcase }: { name: string; whatsappUrl: string | null; showcase: Showcase }) {
    return (
        <>
            {showcase.gallery.length > 0 && (
                <section className="flex flex-col gap-3">
                    <h2 className="text-xl font-medium">Nosso trabalho</h2>
                    <div className="-mx-4 flex snap-x gap-3 overflow-x-auto px-4 pb-1">
                        {showcase.gallery.map((url, index) => (
                            <a key={url} href={url} target="_blank" rel="noopener" className="shrink-0 snap-start">
                                <img src={url} alt={`Foto ${index + 1} de ${name}`} loading="lazy" className="h-48 w-40 rounded-[18px] object-cover shadow-cartao" />
                            </a>
                        ))}
                    </div>
                </section>
            )}

            <section className="cartao flex flex-col gap-4 p-5">
                {showcase.about && (
                    <div className="flex flex-col gap-1.5">
                        <h2 className="text-xl font-medium">Sobre nós</h2>
                        <p className="whitespace-pre-line text-[15px] leading-relaxed text-suave">{showcase.about}</p>
                    </div>
                )}
                <ul className="flex flex-col gap-3 text-[15px]">
                    <ContactItem icon="relogio">{showcase.hoursSummary}</ContactItem>
                    {showcase.address && (
                        <ContactItem icon="local" href={showcase.mapsUrl}>
                            {showcase.address}
                        </ContactItem>
                    )}
                    {showcase.instagramUrl && (
                        <ContactItem icon="instagram" href={showcase.instagramUrl}>
                            @{showcase.instagram}
                        </ContactItem>
                    )}
                    {whatsappUrl && (
                        <ContactItem icon="whatsapp" href={whatsappUrl}>
                            Chamar no WhatsApp
                        </ContactItem>
                    )}
                </ul>
            </section>
        </>
    );
}

function ContactItem({ icon, href, children }: { icon: string; href?: string | null; children: ReactNode }) {
    return (
        <li className="flex items-start gap-2.5">
            <Icon name={icon} className="mt-0.5 h-[18px] w-[18px] text-cafe" />
            {href ? (
                <a href={href} target="_blank" rel="noopener" className="font-semibold text-cafe underline-offset-2 hover:underline">
                    {children}
                </a>
            ) : (
                <span className="text-suave">{children}</span>
            )}
        </li>
    );
}
