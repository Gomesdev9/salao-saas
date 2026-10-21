<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex h-11 items-center justify-center gap-2 rounded-campo border border-linha bg-cartao px-4 text-sm font-semibold text-tinta transition hover:bg-areia focus:outline-none focus:ring-2 focus:ring-caramelo focus:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
