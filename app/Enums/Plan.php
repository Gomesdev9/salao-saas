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

    /** O plano mais indicado, destacado na página de divulgação e já marcado no cadastro. */
    public function isHighlighted(): bool
    {
        return $this === self::Professional;
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
            $features[] = ['text' => 'Financeiro e caixa', 'soon' => true];
            $features[] = ['text' => 'Lembretes por WhatsApp', 'soon' => true];
        }

        if ($this === self::Premium) {
            $features[] = ['text' => 'Agendamento online pelo link da empresa', 'soon' => true];
        }

        return $features;
    }
}
