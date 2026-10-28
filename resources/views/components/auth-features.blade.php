<h1 class="text-4xl font-bold leading-tight sm:text-[40px]">A agenda do seu salão, <em class="italic text-[#D9A57F]">organizada</em>.</h1>
<ul class="flex flex-col gap-3.5 text-base text-menu-texto">
    @foreach (['Agenda do dia com uma coluna por profissional', 'Clientes com WhatsApp, aniversário e observações', 'Serviços com duração e preço, comissão por profissional'] as $feature)
        <li class="flex items-start gap-3">
            <x-icon name="check" class="mt-0.5 h-5 w-5 text-[#D9A57F]" />{{ $feature }}
        </li>
    @endforeach
</ul>
