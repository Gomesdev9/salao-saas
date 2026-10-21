<x-guest-layout>
    <x-slot name="aside">
        <x-auth-features />
    </x-slot>

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
        @csrf

        <div class="flex flex-col gap-1.5">
            <h2 class="text-[32px] font-semibold leading-tight">Entrar</h2>
            <span class="text-[15px] text-suave">Use o e-mail cadastrado do salão.</span>
        </div>

        <x-auth-session-status :status="session('status')" />

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="voce@seusalao.com.br" class="h-12" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="!mb-0" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-cafe hover:text-cafe-escuro">{{ __('Forgot your password?') }}</a>
                @endif
            </div>
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" class="h-12" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-checkbox-input id="remember_me" name="remember" :label="__('Remember me')" />

        <x-primary-button class="h-12 w-full text-[15px]">{{ __('Log in') }}</x-primary-button>

        <p class="text-center text-sm text-suave">
            Ainda não tem conta? <a href="{{ route('register') }}" class="font-semibold text-cafe hover:text-cafe-escuro">Cadastre seu salão</a>
        </p>
    </form>
</x-guest-layout>
