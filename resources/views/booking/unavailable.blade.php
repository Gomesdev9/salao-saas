<x-booking-page :salon="$salon" title="Agendar">
    <x-slot name="heading">Agende pelo <em>WhatsApp</em></x-slot>

    <div class="cartao flex flex-col gap-4 p-5 text-[15px] leading-relaxed text-suave">
        <p>O agendamento pela internet não está disponível agora. Fale com {{ $salon->name }} para marcar o seu horário.</p>
        @if ($salon->whatsappUrl())
            <a href="{{ $salon->whatsappUrl() }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-cafe font-semibold text-white hover:bg-cafe-escuro">
                <x-icon name="whatsapp" class="h-5 w-5" />Chamar no WhatsApp
            </a>
        @endif
    </div>
</x-booking-page>
