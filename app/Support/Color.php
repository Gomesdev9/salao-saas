<?php

namespace App\Support;

/**
 * Cores em hexadecimal ("#925837"), usadas na página pública do salão.
 */
class Color
{
    /** Formato aceito nos formulários. */
    public const PATTERN = '/^#[0-9A-Fa-f]{6}$/';

    /**
     * Os três canais separados por espaço, ex.: "146 88 55", para usar em rgb(var(--cor) / 50%).
     */
    public static function channels(string $hex): string
    {
        return implode(' ', self::rgb($hex));
    }

    /**
     * Mesma cor um pouco mais escura (para o botão com o mouse em cima).
     */
    public static function darken(string $hex, float $amount = 0.18): string
    {
        return sprintf('#%02x%02x%02x', ...array_map(fn (int $channel) => (int) round($channel * (1 - $amount)), self::rgb($hex)));
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
