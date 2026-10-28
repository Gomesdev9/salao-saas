<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-11 items-center justify-center gap-2 rounded-campo bg-cafe px-5 text-sm font-semibold text-white transition hover:bg-cafe-escuro focus:outline-none focus:ring-2 focus:ring-caramelo focus:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
