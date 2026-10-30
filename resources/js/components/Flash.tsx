import { usePage } from '@inertiajs/react';
import Icon from './Icon';

// As telas de perfil usam códigos ("profile-updated") que já aparecem ao lado do botão Salvar
const hidden = ['profile-updated', 'password-updated', 'verification-link-sent'];

/** Aviso verde de sucesso depois de salvar ("Cliente cadastrado.") */
export default function Flash() {
    const { status } = usePage().props.flash;

    if (!status || hidden.includes(status)) {
        return null;
    }

    return (
        <div role="status" className="flex items-center gap-2.5 rounded-campo bg-[#E7F4EC] px-4 py-3 text-sm font-medium text-[#1C5234]">
            <Icon name="check" className="h-[18px] w-[18px]" />
            {status}
        </div>
    );
}
