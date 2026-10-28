@php
    $current = $salon->plan;
    $limit = $current->professionalLimit();
    $days = $salon->trialDaysLeft();
@endphp

<x-page title="Meu plano" subtitle="O plano da sua empresa e o que cada plano inclui.">
    @error('plan')
        <div role="alert" class="rounded-campo bg-mel px-4 py-3 text-sm font-medium text-mel-texto">{{ $message }}</div>
    @enderror

    <section class="cartao flex flex-wrap items-center justify-between gap-6 p-6">
        <div class="flex flex-col gap-1">
            <span class="text-[13px] font-semibold uppercase tracking-wider text-cafe">Plano atual</span>
            <h2 class="text-[28px] font-semibold leading-tight">{{ $current->label() }} <span class="font-sans text-lg font-bold">· {{ $current->formattedPrice() }}/mês</span></h2>
            @if ($salon->onTrial())
                <span class="text-sm text-suave">
                    Teste grátis: {{ $days === 1 ? 'falta' : 'faltam' }} <strong class="text-tinta">{{ $days }} {{ $days === 1 ? 'dia' : 'dias' }}</strong>
                    (até {{ $salon->trial_ends_at->translatedFormat('j \d\e F') }}). Você pode trocar de plano quando quiser.
                </span>
            @else
                <span class="text-sm text-suave">Você pode trocar de plano quando quiser.</span>
            @endif
        </div>
        <div class="flex w-full flex-col gap-2 sm:w-[260px]">
            <span class="flex justify-between gap-3 text-sm">
                Profissionais ativos
                <strong>{{ $activeProfessionals }} de {{ $limit ?? 'ilimitados' }}</strong>
            </span>
            @if ($limit)
                <div class="h-2 overflow-hidden rounded-full bg-areia" role="presentation">
                    <div class="h-full rounded-full {{ $activeProfessionals > $limit ? 'bg-perigo' : 'bg-cafe' }}" style="width: {{ min(100, round($activeProfessionals / $limit * 100)) }}%"></div>
                </div>
                @if ($activeProfessionals > $limit)
                    <span class="text-xs font-medium text-perigo">Acima do limite do plano: novos profissionais só entram como inativos.</span>
                @endif
            @endif
        </div>
    </section>

    <div class="grid gap-6 pt-3 lg:grid-cols-3">
        @foreach ($plans as $plan)
            <x-plan-card :plan="$plan" :current="$plan === $current">
                @if ($plan === $current)
                    <span class="inline-flex h-12 w-full items-center justify-center rounded-full border text-[15px] font-semibold {{ $plan->isHighlighted() ? 'border-menu-ativo text-menu-texto' : 'border-linha text-suave' }}">Plano atual</span>
                @else
                    <form method="POST" action="{{ route('plan.update') }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="plan" value="{{ $plan->value }}">
                        <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-full text-[15px] font-semibold transition {{ $plan->isHighlighted() ? 'bg-pele text-tinta hover:bg-white' : 'bg-cafe text-white hover:bg-cafe-escuro' }}">
                            Mudar para o {{ $plan->label() }}
                        </button>
                    </form>
                @endif
            </x-plan-card>
        @endforeach
    </div>

    <p class="text-sm text-suave">O que está marcado como "Em breve" entra no plano assim que ficar pronto.</p>
</x-page>
