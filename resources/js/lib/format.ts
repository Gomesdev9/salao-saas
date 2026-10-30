const number = (value: number, digits: number) =>
    value.toLocaleString('pt-BR', { minimumFractionDigits: digits, maximumFractionDigits: digits });

/** "R$ 1.234,56" (igual ao App\Support\Money::format) */
export const money = (value: number | string | null | undefined) => `R$ ${number(Number(value ?? 0), 2)}`;

/** "R$ 120" quando não há centavos, senão "R$ 45,90" */
export const shortMoney = (value: number) => `R$ ${number(value, Number.isInteger(value) ? 0 : 2)}`;

/** Só o primeiro nome: "Ana Paula Souza" vira "Ana" */
export const firstName = (name: string) => name.trim().split(/\s+/)[0] ?? '';

/** "1 cliente cadastrado" / "3 clientes cadastrados" */
export const plural = (count: number, one: string, many: string) => `${count} ${count === 1 ? one : many}`;

/** Lê "1.234,56", "150" ou "R$ 45,90" (mesmas regras do App\Support\Money::parse) */
export const parseMoney = (text: string | number | null | undefined): number | null => {
    let value = String(text ?? '').replace(/[^\d,.]/g, '');
    if (value === '') return null;
    if (value.includes(',')) {
        value = value.replace(/\./g, '').replace(',', '.');
    } else if (/^\d{1,3}(\.\d{3})+$/.test(value)) {
        value = value.replace(/\./g, '');
    }
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
};

/** Junta classes do Tailwind ignorando as vazias */
export const cx = (...classes: (string | false | null | undefined)[]) => classes.filter(Boolean).join(' ');
