import Alpine from 'alpinejs';
import pagamento from './pagamento';

window.Alpine = Alpine;

Alpine.data('pagamento', pagamento);

Alpine.start();
