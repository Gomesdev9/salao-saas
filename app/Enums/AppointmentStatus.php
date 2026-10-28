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
     * Classes do Tailwind (cores do design) para o selo e o bloco na agenda.
     */
    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-[#F9E3D5] border-[#BC7F5C] text-[#78472A]',
            self::Confirmed => 'bg-[#E6F0FA] border-[#3B7BC0] text-[#163E66]',
            self::Done => 'bg-[#E7F4EC] border-[#3E9A63] text-[#1C5234]',
            self::NoShow => 'bg-[#FBE9E7] border-[#C8553D] text-[#8A2A1B]',
            self::Canceled => 'bg-[#F2EDE7] border-[#C9BCAF] text-[#6F5947]',
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
