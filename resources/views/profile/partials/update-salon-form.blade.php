@php
    $salon = $user->salon;
@endphp

<section>
    <header>
        <h2 class="text-xl font-semibold">Dados da empresa</h2>
        <p class="mt-1 text-sm text-suave">A chave Pix aparece na tela de receber pagamento, pronta para copiar.</p>
    </header>

    <form method="post" action="{{ route('profile.salon') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="salon_name" value="Nome da empresa" />
            <x-text-input id="salon_name" name="salon_name" type="text" class="mt-1" :value="old('salon_name', $salon->name)" required />
            <x-input-error class="mt-2" :messages="$errors->salon->get('salon_name')" />
        </div>

        <div>
            <x-input-label for="pix_key" value="Chave Pix" />
            <x-text-input id="pix_key" name="pix_key" type="text" class="mt-1" :value="old('pix_key', $salon->pix_key)" placeholder="CNPJ, e-mail, telefone ou chave aleatória" />
            <x-input-error class="mt-2" :messages="$errors->salon->get('pix_key')" />
        </div>

        <x-primary-button>Salvar</x-primary-button>
    </form>
</section>
