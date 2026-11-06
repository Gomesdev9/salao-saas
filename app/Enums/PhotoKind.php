<?php

namespace App\Enums;

/**
 * Lugar da foto na página pública do salão.
 */
enum PhotoKind: string
{
    case Logo = 'logo';
    case Cover = 'capa';
    case Gallery = 'galeria';

    /** Quantas fotos de cada tipo o salão pode ter. */
    public function limit(): int
    {
        return match ($this) {
            self::Logo, self::Cover => 1,
            self::Gallery => 8,
        };
    }
}
