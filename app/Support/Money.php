<?php

namespace App\Support;

/**
 * Valores em reais: mostrar ("R$ 1.234,56") e ler o que a pessoa digitou ("1.234,56", "120", "R$ 45,90").
 */
class Money
{
    public static function format(float|int|string|null $value): string
    {
        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }

    /**
     * Converte o texto digitado em número. Devolve null se não for um valor válido.
     */
    public static function parse(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $text = preg_replace('/[^\d,.]/', '', (string) $value);

        if ($text === '') {
            return null;
        }

        if (str_contains($text, ',')) {
            // Formato brasileiro: ponto separa milhar e vírgula separa centavos
            $text = str_replace(['.', ','], ['', '.'], $text);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $text)) {
            // "1.500" é mil e quinhentos, não um e meio
            $text = str_replace('.', '', $text);
        }

        return is_numeric($text) ? round((float) $text, 2) : null;
    }
}
