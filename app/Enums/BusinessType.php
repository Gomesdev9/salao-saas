<?php

namespace App\Enums;

/**
 * Tipo de negócio informado pela empresa no cadastro.
 */
enum BusinessType: string
{
    case BeautySalon = 'salao';
    case AestheticsClinic = 'estetica';
    case Barbershop = 'barbearia';
    case NailStudio = 'esmalteria';
    case BrowStudio = 'sobrancelhas';
    case Other = 'outro';

    public function label(): string
    {
        return match ($this) {
            self::BeautySalon => 'Salão de beleza',
            self::AestheticsClinic => 'Clínica de estética',
            self::Barbershop => 'Barbearia',
            self::NailStudio => 'Esmalteria',
            self::BrowStudio => 'Sobrancelhas e cílios',
            self::Other => 'Outro',
        };
    }
}
