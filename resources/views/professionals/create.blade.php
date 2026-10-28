<x-page title="Novo profissional" :back="route('professionals.index')" back-label="Voltar para profissionais">
    @unless ($canActivate)
        @php($plan = auth()->user()->salon->plan)
        <div role="status" class="max-w-3xl rounded-campo bg-mel px-4 py-3 text-sm leading-relaxed text-mel-texto">
            O plano {{ $plan->label() }} permite até {{ $plan->professionalLimit() }} profissionais ativos, e a sua equipe já chegou nesse número.
            Você pode cadastrar como inativo ou <a href="{{ route('plan.edit') }}" class="font-semibold underline">mudar de plano</a>.
        </div>
    @endunless
    <form method="POST" action="{{ route('professionals.store') }}" class="cartao max-w-3xl">
        @csrf
        @include('professionals._form')
    </form>
</x-page>
