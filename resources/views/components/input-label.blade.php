@props(['value'])

<label {{ $attributes->merge(['class' => 'mb-1.5 block text-sm font-semibold text-tinta']) }}>
    {{ $value ?? $slot }}
</label>
