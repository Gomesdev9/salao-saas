import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import type { ResolvedComponent } from '@inertiajs/react';

// Cada tela fica em resources/js/pages (ex.: o controller manda 'clients/index' e abre pages/clients/index.tsx)
const pages = import.meta.glob<{ default: ResolvedComponent }>('./pages/**/*.tsx');

createInertiaApp({
    // As telas já mandam o título completo, ex.: "Clientes · Salão SaaS"
    title: (title) => title,
    resolve: async (name) => {
        const page = pages[`./pages/${name}.tsx`];

        if (!page) {
            throw new Error(`Tela não encontrada: ${name}`);
        }

        return (await page()).default;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#925837',
    },
});
