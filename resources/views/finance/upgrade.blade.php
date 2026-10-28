<x-page title="Financeiro">
    <section class="cartao flex max-w-[640px] flex-col items-start gap-4 p-6 sm:p-8">
        <span class="flex h-12 w-12 items-center justify-center rounded-campo bg-pele text-cafe-escuro"><x-icon name="financeiro" class="h-[22px] w-[22px]" /></span>
        <h2 class="text-2xl font-semibold">O Financeiro faz parte dos planos Profissional e Premium</h2>
        <p class="text-[15px] leading-relaxed text-suave">
            Sua empresa está no plano {{ $salon->plan->label() }}. Mudando de plano, você vê quanto recebeu no mês, as despesas a vencer,
            o lucro estimado e quanto pagar de comissão a cada profissional. Os pagamentos que você já recebeu pela agenda ficam guardados.
        </p>
        <x-link-button :href="route('plan.edit')">Ver planos</x-link-button>
    </section>
</x-page>
