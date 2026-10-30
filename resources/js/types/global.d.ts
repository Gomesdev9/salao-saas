import type { route as ziggyRoute } from 'ziggy-js';
import type { SharedProps } from './index';

declare global {
    // Rotas do Laravel no navegador (@routes no app.blade.php), ex.: route('clients.edit', 3)
    var route: typeof ziggyRoute;
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: SharedProps;
    }
}
