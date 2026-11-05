import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PrimaryButton } from './Buttons';

/** Rodapé dos formulários de cadastro: remover (à esquerda), cancelar e salvar */
export default function FormFooter({ cancel, remove, processing }: { cancel: string; remove?: ReactNode; processing?: boolean }) {
    return (
        <div className="flex flex-wrap items-center gap-3 border-t border-linha px-6 py-4">
            {remove && <div className="mr-auto">{remove}</div>}
            <Link href={cancel} className="ml-auto inline-flex h-11 items-center px-4 text-sm font-semibold hover:text-cafe">
                Cancelar
            </Link>
            <PrimaryButton disabled={processing}>Salvar</PrimaryButton>
        </div>
    );
}
