@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-campo bg-[#E7F4EC] px-4 py-3 text-sm font-medium text-[#1C5234]']) }}>
        {{ $status }}
    </div>
@endif
