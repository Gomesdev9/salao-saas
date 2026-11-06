// Itens do menu: rota, quais rotas deixam o item marcado, ícone e nome
export type NavItem = { route: string; active: string; icon: string; label: string };

export const mainItems: NavItem[] = [
    { route: 'dashboard', active: 'dashboard', icon: 'painel', label: 'Painel' },
    { route: 'appointments.index', active: 'appointments.*', icon: 'agenda', label: 'Agenda' },
    { route: 'booking-page.edit', active: 'booking-page.*', icon: 'pagina', label: 'Minha página' },
    { route: 'clients.index', active: 'clients.*', icon: 'clientes', label: 'Clientes' },
    { route: 'professionals.index', active: 'professionals.*', icon: 'profissionais', label: 'Profissionais' },
    { route: 'services.index', active: 'services.*', icon: 'servicos', label: 'Serviços' },
    { route: 'finance.index', active: 'finance.*', icon: 'financeiro', label: 'Financeiro' },
];

// No celular, os três primeiros ficam no menu de baixo; "Minha página" vai para o "Mais" (não cabe na barra)
export const bottomItems = mainItems.filter((item) => item.route !== 'booking-page.edit').slice(0, 3);

export const isActive = (...patterns: string[]) => patterns.some((pattern) => route().current(pattern));
