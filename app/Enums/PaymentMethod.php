<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Pix = 'pix';
    case Cash = 'cash';
    case Debit = 'debit';
    case Credit = 'credit';
    case BankSlip = 'bank_slip';

    public function label(): string
    {
        return match ($this) {
            self::Pix => 'Pix',
            self::Cash => 'Dinheiro',
            self::Debit => 'Débito',
            self::Credit => 'Crédito',
            self::BankSlip => 'Boleto',
        };
    }

    /**
     * Nome por extenso, usado na visão do mês ("Cartão de crédito").
     */
    public function longLabel(): string
    {
        return match ($this) {
            self::Debit => 'Cartão de débito',
            self::Credit => 'Cartão de crédito',
            default => $this->label(),
        };
    }

    /**
     * Formas aceitas no balcão para pagar um atendimento (boleto fica só para despesas).
     *
     * @return list<self>
     */
    public static function forAppointments(): array
    {
        return [self::Pix, self::Cash, self::Debit, self::Credit];
    }
}
