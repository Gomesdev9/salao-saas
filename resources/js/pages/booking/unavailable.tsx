import BookingLayout, { type BookingSalon, WhatsappButton } from '@/layouts/BookingLayout';

export default function Unavailable({ salon }: { salon: BookingSalon }) {
    return (
        <BookingLayout
            salon={salon}
            title="Agendar"
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
        </BookingLayout>
    );
}
