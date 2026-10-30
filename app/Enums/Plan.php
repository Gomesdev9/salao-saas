<?php

namespace App\Enums;

/**
 * Planos pagos do sistema. Toda empresa nova começa com TRIAL_DAYS dias grátis no plano escolhido.
 */
enum Plan: string
{
    case Essential = 'essencial';
    case Professional = 'profissional';
    case Premium = 'premium';

    public const TRIAL_DAYS = 14;

    public function label(): string
    {
        return match ($this) {
            self::Essential => 'Essencial',
            self::Professional => 'Profissional',
            self::Premium => 'Premium',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Essential => 'Para quem atende sozinho ou em dupla.',
            self::Professional => 'Para salões e clínicas com equipe.',
            self::Premium => 'Para equipes grandes e redes.',
        };
    }

    /** Mensalidade em reais. */
    public function price(): float
    {
        return match ($this) {
            self::Essential => 49.90,
            self::Professional => 89.90,
            self::Premium => 149.90,
        };
    }

    /** Ex.: "R$ 89,90" */
    public function formattedPrice(): string
    {
        return 'R$ '.number_format($this->price(), 2, ',', '.');
    }

    /** Quantos profissionais ativos o plano permite. Null = sem limite. */
    public function professionalLimit(): ?int
    {
        return match ($this) {
            self::Essential => 2,
            self::Professional => 6,
            self::Premium => null,
        };
    }

    /** Financeiro (visão do mês, lançamentos e comissões) fica nos planos Profissional e Premium. */
    public function includesFinance(): bool
    {
        return $this !== self::Essential;
    }

    /** Agendamento online pelo link da empresa também fica nos planos Profissional e Premium. */
    public function includesOnlineBooking(): bool
    {
        return $this !== self::Essential;
    }

    /** O plano mais indicado, destacado na página de divulgação e já marcado no cadastro. */
    public function isHighlighted(): bool
    {
        return $this === self::Professional;
    }

    /**
     * Dados para as telas em React (cartão do plano na página de divulgação e em "Meu plano").
     *
     * @return array{value: string, label: string, description: string, price: float, formattedPrice: string, highlighted: bool, professionalLimit: ?int, features: list<array{text: string, soon: bool}>}
     */
    public function toProps(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'description' => $this->description(),
            'price' => $this->price(),
            'formattedPrice' => $this->formattedPrice(),
            'highlighted' => $this->isHighlighted(),
            'professionalLimit' => $this->professionalLimit(),
            'features' => $this->features(),
        ];
    }

    /**
     * Todos os planos, prontos para as telas.
     *
     * @return list<array<string, mixed>>
     */
    public static function allProps(): array
    {
        return array_map(fn (self $plan) => $plan->toProps(), self::cases());
    }

    /**
     * O que o plano inclui. "soon" marca o que ainda vai chegar no sistema.
     *
     * @return list<array{text: string, soon: bool}>
     */
    public function features(): array
    {
        $limit = $this->professionalLimit();

        $features = [
            ['text' => $limit ? "Até {$limit} profissionais ativos" : 'Profissionais ilimitados', 'soon' => false],
            ['text' => 'Agenda, clientes e serviços', 'soon' => false],
            ['text' => 'Comissões e painel do dia', 'soon' => false],
        ];

        if ($this !== self::Essential) {
            $features[] = ['text' => 'Financeiro e caixa', 'soon' => false];
            $features[] = ['text' => 'Agendamento online pelo link da empresa', 'soon' => false];
            $features[] = ['text' => 'Lembretes por WhatsApp', 'soon' => true];
        }

        return $features;
    }
}
