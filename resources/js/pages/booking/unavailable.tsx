import { type Showcase, ShowcaseDetails, ShowcaseHero } from '@/components/BookingShowcase';
import BookingLayout, { type BookingSalon, WhatsappButton } from '@/layouts/BookingLayout';

export default function Unavailable({ salon, showcase }: { salon: BookingSalon; showcase: Showcase }) {
    return (
        <BookingLayout
            salon={salon}
            title="Agendar"
            hero={<ShowcaseHero name={salon.name} city={salon.city} logoUrl={salon.logoUrl} showcase={showcase} />}
            heading={
                <>
                    Agende pelo <em>WhatsApp</em>
                </>
            }
        >
            <div className="cartao flex flex-col gap-4 p-5 text-[15px] leading-relaxed text-suave">
                <p>O agendamento pela internet não está disponível agora. Fale com {salon.name} para marcar o seu horário.</p>
                {salon.whatsappUrl && <WhatsappButton href={salon.whatsappUrl}>Chamar no WhatsApp</WhatsappButton>}
            </div>

            <ShowcaseDetails name={salon.name} whatsappUrl={null} showcase={showcase} />
        </BookingLayout>
    );
}
