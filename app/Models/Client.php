<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSalon;
use App\Support\Phone;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        return Phone::whatsappUrl($this->whatsapp);
    }

    /** Chave do link "Meus horários", criada no primeiro agendamento pela página do salão. */
    public function accessToken(): string
    {
        if (! $this->access_token) {
            $this->forceFill(['access_token' => Str::random(40)])->save();
        }

        return $this->access_token;
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
