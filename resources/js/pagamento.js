// Tela "Receber pagamento": recalcula total, troco e comissão enquanto a pessoa escolhe desconto e forma.
// Os valores de verdade são calculados de novo no servidor (PaymentController) ao confirmar.

const brl = (value) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);

// Lê "1.234,56", "150" ou "R$ 45,90" (mesmas regras do App\Support\Money::parse)
const parseMoney = (text) => {
    let value = String(text ?? '').replace(/[^\d,.]/g, '');
    if (value === '') return null;
    if (value.includes(',')) {
        value = value.replace(/\./g, '').replace(',', '.');
    } else if (/^\d{1,3}(\.\d{3})+$/.test(value)) {
        value = value.replace(/\./g, '');
    }
    const number = Number(value);
    return Number.isFinite(number) ? number : null;
};

export default ({ subtotal, percent, method, discount, received, labels }) => ({
    subtotal,
    percent,
    method,
    discount,
    received,
    labels,

    get total() {
        return Math.round(this.subtotal * (100 - this.discount)) / 100;
    },
    get change() {
        const value = parseMoney(this.received);
        return value === null ? null : value - this.total;
    },
    get commission() {
        return Math.round(this.total * this.percent) / 100;
    },
    brl,
});
