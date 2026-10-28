@php
    // As telas de perfil do Breeze usam códigos ("profile-updated") que já aparecem ao lado do botão Salvar
    $status = session('status');
    $show = $status && ! in_array($status, ['profile-updated', 'password-updated', 'verification-link-sent']);
@endphp

@if ($show)
    <div role="status" class="flex items-center gap-2.5 rounded-campo bg-[#E7F4EC] px-4 py-3 text-sm font-medium text-[#1C5234]">
        <x-icon name="check" class="h-[18px] w-[18px]" />
        {{ $status }}
    </div>
@endif
