<?php

namespace App\Support;

/**
 * Telefones e WhatsApp brasileiros, digitados de qualquer jeito: "(11) 98765-4321", "11987654321".
 */
class Phone
{
    /** Só os números, ex.: "11987654321". */
    public static function digits(?string $number): string
    {
        return preg_replace('/\D/', '', (string) $number);
    }

    /**
     * Link para abrir a conversa no WhatsApp (wa.me), ou null se o número for curto demais.
     */
    public static function whatsappUrl(?string $number): ?string
    {
        $digits = self::digits($number);

        if (strlen($digits) < 10) {
            return null;
        }

        // Número brasileiro sem DDI: acrescenta o 55
        if (strlen($digits) <= 11) {
            $digits = '55'.$digits;
        }

        return "https://wa.me/{$digits}";
    }
}
