@props(['label', 'checked' => false])

<label class="inline-flex min-h-11 items-center gap-2.5">
    <input type="checkbox" @checked($checked) {{ $attributes->merge(['class' => 'h-[18px] w-[18px] rounded border-linha-forte text-cafe focus:ring-caramelo']) }}>
    <span class="text-sm text-tinta">{{ $label }}</span>
</label>
