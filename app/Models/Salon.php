<?php

namespace App\Models;

use App\Enums\BusinessType;
use App\Enums\Plan;
use App\Support\Phone;
use Carbon\CarbonInterface;
use Database\Factories\SalonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'business_type', 'plan', 'trial_ends_at', 'phone', 'city', 'pix_key'])]
class Salon extends Model
{
    /** @use HasFactory<SalonFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'business_type' => BusinessType::class,
            'plan' => Plan::class,
            'trial_ends_at' => 'datetime',
        ];
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at?->isFuture() ?? false;
    }

    /** Dias que faltam do teste grátis, contando hoje. */
    public function trialDaysLeft(): int
    {
        return $this->onTrial() ? (int) ceil(now()->diffInDays($this->trial_ends_at)) : 0;
    }

    public function activeProfessionalsCount(): int
    {
        return $this->professionals()->where('active', true)->count();
    }

    /** Se ainda cabe mais um profissional ativo no plano. */
    public function canActivateProfessional(): bool
    {
        $limit = $this->plan->professionalLimit();

        return $limit === null || $this->activeProfessionalsCount() < $limit;
    }

    /** Durante o teste grátis tudo fica liberado; depois, depende do plano. */
    public function hasFinance(): bool
    {
        return $this->onTrial() || $this->plan->includesFinance();
    }

    /** Se a página pública de agendamento aceita horários (mesma regra do Financeiro). */
    public function hasOnlineBooking(): bool
    {
        return $this->onTrial() || $this->plan->includesOnlineBooking();
    }

    /** Endereço da página em que a cliente agenda sozinha. */
    public function bookingUrl(): string
    {
        return route('booking.service', $this);
    }

    public function whatsappUrl(): ?string
    {
        return Phone::whatsappUrl($this->phone);
    }

    /**
     * Horário de funcionamento por dia da semana (1 = segunda ... 7 = domingo), null = fechado.
     * Por enquanto é o mesmo para todas as empresas.
     *
     * @return array<int, array{0: string, 1: string}|null>
     */
    public function openingHours(): array
    {
        return [
            1 => ['09:00', '19:00'],
            2 => ['09:00', '19:00'],
            3 => ['09:00', '19:00'],
            4 => ['09:00', '19:00'],
            5 => ['09:00', '19:00'],
            6 => ['09:00', '14:00'],
            7 => null,
        ];
    }

    /** @return array{0: string, 1: string}|null */
    public function hoursOn(CarbonInterface $day): ?array
    {
        return $this->openingHours()[$day->dayOfWeekIso];
    }

    /** Ex.: "Seg a sex, 9h às 19h · Sáb, 9h às 14h · Domingo fechado" */
    public function openingHoursSummary(): string
    {
        return 'Seg a sex, 9h às 19h · Sáb, 9h às 14h · Domingo fechado';
    }

    /**
     * Gera um slug único a partir do nome, ex.: "Salão da Maria" -> "salao-da-maria-2".
     */
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'salao';
        $slug = $base;

        for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function professionals(): HasMany
    {
        return $this->hasMany(Professional::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
