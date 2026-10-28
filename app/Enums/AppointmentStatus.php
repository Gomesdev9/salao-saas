<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Confirmed = 'confirmed';
    case Done = 'done';
    case NoShow = 'no_show';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Agendado',
            self::Confirmed => 'Confirmado',
            self::Done => 'Atendido',
            self::NoShow => 'Faltou',
            self::Canceled => 'Cancelado',
        };
    }

    /**
     * Classes do Tailwind para colorir o horário na agenda.
     */
    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-indigo-50 border-indigo-300 text-indigo-900',
            self::Confirmed => 'bg-sky-50 border-sky-400 text-sky-900',
            self::Done => 'bg-green-50 border-green-400 text-green-900',
            self::NoShow => 'bg-amber-50 border-amber-400 text-amber-900',
            self::Canceled => 'bg-gray-50 border-gray-300 text-gray-500 line-through',
        };
    }

    /**
     * Status que ocupam o horário do profissional.
     *
     * @return list<self>
     */
    public static function occupying(): array
    {
        return [self::Scheduled, self::Confirmed, self::Done];
    }
}
