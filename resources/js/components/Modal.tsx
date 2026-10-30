import { useEffect, type ReactNode } from 'react';

/** Janela por cima da tela, com fundo escuro. Fecha com Esc ou clicando fora. */
export default function Modal({ show, onClose, children }: { show: boolean; onClose: () => void; children: ReactNode }) {
    useEffect(() => {
        if (!show) {
            return;
        }

        const onKey = (event: KeyboardEvent) => event.key === 'Escape' && onClose();
        document.body.classList.add('overflow-y-hidden');
        window.addEventListener('keydown', onKey);

        return () => {
            document.body.classList.remove('overflow-y-hidden');
            window.removeEventListener('keydown', onKey);
        };
    }, [show, onClose]);

    if (!show) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0" role="dialog" aria-modal="true">
            <div className="fixed inset-0" onClick={onClose}>
                <div className="absolute inset-0 bg-tinta opacity-60" />
            </div>
            <div className="relative mb-6 overflow-hidden rounded-cartao bg-cartao shadow-xl sm:mx-auto sm:w-full sm:max-w-2xl">{children}</div>
        </div>
    );
}
