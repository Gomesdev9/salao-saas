@props(['label', 'checked' => false])

<label class="inline-flex items-center gap-2">
    <input type="checkbox" @checked($checked) {{ $attributes->merge(['class' => 'rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500']) }}>
    <span class="text-sm text-gray-700">{{ $label }}</span>
</label>
