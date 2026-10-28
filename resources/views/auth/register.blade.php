<x-guest-layout>
    <x-slot name="aside">
        <h1 class="text-4xl font-bold leading-tight">Comece em três passos</h1>
        <ol class="flex flex-col gap-4 text-base text-menu-texto">
            @foreach (['Cadastre sua empresa', 'Cadastre serviços e profissionais', 'Comece a agendar'] as $i => $step)
                <li class="flex items-center gap-3.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full font-bold {{ $i === 0 ? 'bg-cafe text-white' : 'border border-[#5A4334]' }}">{{ $i + 1 }}</span>
                    {{ $step }}
                </li>
            @endforeach
        </ol>
        <p class="flex items-center gap-2.5 text-[15px] text-menu-texto"><x-icon name="check" class="h-[18px] w-[18px] text-[#D9A57F]" />{{ \App\Enums\Plan::TRIAL_DAYS }} dias grátis em qualquer plano, sem cartão.</p>
    </x-slot>

    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-5">
        @csrf

        <div class="flex flex-col gap-1.5">
            <h2 class="text-[32px] font-semibold leading-tight">Cadastre sua empresa</h2>
            <span class="text-[15px] text-suave">Você será o proprietário e administrador da conta.</span>
        </div>

        <fieldset class="flex flex-col gap-2.5">
            <legend class="pb-2.5 text-[13px] font-bold uppercase tracking-wider text-cafe">O plano</legend>
            @php($chosen = old('plan', $selectedPlan->value))
            @foreach ($plans as $plan)
                <label class="flex min-h-14 cursor-pointer items-center gap-3 rounded-campo border border-linha bg-creme px-4 py-2.5 transition hover:border-linha-forte has-[:checked]:border-cafe has-[:checked]:bg-pele/50 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-cafe">
                    <input type="radio" name="plan" value="{{ $plan->value }}" @checked($chosen === $plan->value) required class="h-[18px] w-[18px] shrink-0 border-linha-forte text-cafe focus:ring-caramelo">
                    <span class="flex min-w-0 flex-1 flex-col">
                        <span class="flex flex-wrap items-center gap-x-2 text-[15px] font-bold">
                            {{ $plan->label() }}
                            @if ($plan->isHighlighted())
                                <span class="text-xs font-semibold text-cafe">Mais escolhido</span>
                            @endif
                        </span>
                        <span class="text-[13px] text-suave">{{ $plan->features()[0]['text'] }}</span>
                    </span>
                    <span class="shrink-0 text-right text-[15px] font-bold">{{ $plan->formattedPrice() }}<span class="block text-xs font-normal text-suave">por mês</span></span>
                </label>
            @endforeach
            <x-input-error :messages="$errors->get('plan')" />
        </fieldset>

        <fieldset class="flex flex-col gap-3.5">
            <legend class="pb-2.5 text-[13px] font-bold uppercase tracking-wider text-cafe">A empresa</legend>
            <div>
                <x-input-label for="salon_name" value="Nome da empresa" />
                <x-text-input id="salon_name" type="text" name="salon_name" :value="old('salon_name')" required autofocus autocomplete="organization" placeholder="Ex.: Studio Bela Vista" class="h-12" />
                <x-input-error :messages="$errors->get('salon_name')" />
            </div>
            <div>
                <x-input-label for="business_type" value="Tipo de negócio" />
                <x-select-input id="business_type" name="business_type" required class="h-12">
                    <option value="" disabled @selected(! old('business_type'))>Escolha uma opção</option>
                    @foreach ($businessTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('business_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('business_type')" />
            </div>
            <div class="grid gap-3.5 sm:grid-cols-2">
                <div>
                    <x-input-label for="phone" value="WhatsApp da empresa" />
                    <x-text-input id="phone" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" inputmode="tel" placeholder="(11) 99999-9999" class="h-12" />
                    <x-input-error :messages="$errors->get('phone')" />
                </div>
                <div>
                    <x-input-label for="city">Cidade <span class="font-normal text-suave">(opcional)</span></x-input-label>
                    <x-text-input id="city" type="text" name="city" :value="old('city')" autocomplete="address-level2" placeholder="Ex.: Curitiba - PR" class="h-12" />
                    <x-input-error :messages="$errors->get('city')" />
                </div>
            </div>
        </fieldset>

        <fieldset class="flex flex-col gap-3.5">
            <legend class="pb-2.5 text-[13px] font-bold uppercase tracking-wider text-cafe">Seus dados de acesso</legend>
            <div>
                <x-input-label for="name" value="Seu nome" />
                <x-text-input id="name" type="text" name="name" :value="old('name')" required autocomplete="name" class="h-12" />
                <x-input-error :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="voce@suaempresa.com.br" class="h-12" />
                <x-input-error :messages="$errors->get('email')" />
            </div>
            <div class="grid gap-3.5 sm:grid-cols-2">
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" type="password" name="password" required autocomplete="new-password" class="h-12" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="h-12" />
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" class="!mt-0" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="!mt-0" />
        </fieldset>

        <x-primary-button class="h-12 w-full text-[15px]">Começar meu teste grátis</x-primary-button>

        <p class="text-center text-sm text-suave">
            {{ __('Already registered?') }} <a href="{{ route('login') }}" class="font-semibold text-cafe hover:text-cafe-escuro">{{ __('Log in') }}</a>
        </p>
    </form>
</x-guest-layout>
