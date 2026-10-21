<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSalon;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['name', 'whatsapp', 'birthday', 'notes'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToSalon, HasFactory;

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
        ];
    }

    /**
     * Link para abrir a conversa no WhatsApp (wa.me), ou null se não houver número.
     */
    public function whatsappUrl(): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $this->whatsapp);

        if (strlen($digits) < 10) {
            return null;
        }

        // Número brasileiro sem DDI: acrescenta o 55
        if (strlen($digits) <= 11) {
            $digits = '55'.$digits;
        }

        return "https://wa.me/{$digits}";
    }

    /**
     * Próximo aniversário a partir de hoje (ou null se não tiver data).
     */
    public function nextBirthday(): ?Carbon
    {
        if (! $this->birthday) {
            return null;
        }

        // 29/02 vira 28/02 nos anos que não são bissextos
        $inYear = fn (int $year) => Carbon::create($year, $this->birthday->month, 1)
            ->setDay(min($this->birthday->day, Carbon::create($year, $this->birthday->month, 1)->daysInMonth));

        $next = $inYear(today()->year);

        return $next->lt(today()) ? $inYear(today()->year + 1) : $next;
    }

    public function hasBirthdayWithin(int $days = 7): bool
    {
        return (bool) $this->nextBirthday()?->lte(today()->addDays($days));
    }
}
