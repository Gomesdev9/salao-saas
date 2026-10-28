<x-guest-layout>
    <x-slot name="aside">
        <h1 class="text-4xl font-bold leading-tight">Comece em três passos</h1>
        <ol class="flex flex-col gap-4 text-base text-menu-texto">
            @foreach (['Crie a conta do salão', 'Cadastre serviços e profissionais', 'Comece a agendar'] as $i => $step)
                <li class="flex items-center gap-3.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full font-bold {{ $i === 0 ? 'bg-cafe text-white' : 'border border-[#5A4334]' }}">{{ $i + 1 }}</span>
                    {{ $step }}
                </li>
            @endforeach
        </ol>
    </x-slot>

    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-5">
        @csrf

        <div class="flex flex-col gap-1.5">
            <h2 class="text-[32px] font-semibold leading-tight">Cadastre seu salão</h2>
            <span class="text-[15px] text-suave">Você será o administrador da conta.</span>
        </div>

        <fieldset class="flex flex-col gap-3.5">
            <legend class="pb-2.5 text-[13px] font-bold uppercase tracking-wider text-cafe">O salão</legend>
            <div>
                <x-input-label for="salon_name" value="Nome do salão" />
                <x-text-input id="salon_name" type="text" name="salon_name" :value="old('salon_name')" required autofocus placeholder="Ex.: Studio Bela Vista" class="h-12" />
                <x-input-error :messages="$errors->get('salon_name')" />
            </div>
        </fieldset>

        <fieldset class="flex flex-col gap-3.5">
            <legend class="pb-2.5 text-[13px] font-bold uppercase tracking-wider text-cafe">Seus dados</legend>
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" type="text" name="name" :value="old('name')" required autocomplete="name" class="h-12" />
                <x-input-error :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" class="h-12" />
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

        <x-primary-button class="h-12 w-full text-[15px]">Criar conta do salão</x-primary-button>

        <p class="text-center text-sm text-suave">
            {{ __('Already registered?') }} <a href="{{ route('login') }}" class="font-semibold text-cafe hover:text-cafe-escuro">{{ __('Log in') }}</a>
        </p>
    </form>
</x-guest-layout>
