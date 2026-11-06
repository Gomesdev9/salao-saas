<?php

namespace App\Models;

use App\Enums\BusinessType;
use App\Enums\PhotoKind;
use App\Enums\Plan;
use App\Support\Color;
use App\Support\Phone;
use Carbon\CarbonInterface;
use Database\Factories\SalonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'business_type', 'plan', 'trial_ends_at', 'phone', 'city', 'pix_key',
    'page_accent_color', 'page_background_color', 'page_headline', 'page_about', 'address', 'instagram'])]
class Salon extends Model
{
    /** @use HasFactory<SalonFactory> */
    use HasFactory;

    /** Cor dos botões e destaques da página pública, quando o salão não escolheu outra. */
    public const DEFAULT_ACCENT_COLOR = '#925837';

    /** Cor do fundo da página pública, quando o salão não escolheu outra. */
    public const DEFAULT_BACKGROUND_COLOR = '#FEFAF3';

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

    public function instagramUrl(): ?string
    {
        return $this->instagram ? "https://instagram.com/{$this->instagram}" : null;
    }

    /** Endereço aberto no Google Maps. */
    public function mapsUrl(): ?string
    {
        return $this->address ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($this->address) : null;
    }

    /**
     * Cores da página pública. Os canais ("146 88 55") vão para as variáveis CSS do BookingLayout.
     *
     * @return array{accent: string, accentChannels: string, accentDarkChannels: string, background: string}
     */
    public function pageTheme(): array
    {
        $accent = $this->page_accent_color ?: self::DEFAULT_ACCENT_COLOR;

        return [
            'accent' => $accent,
            'accentChannels' => Color::channels($accent),
            'accentDarkChannels' => Color::channels(Color::darken($accent)),
            'background' => $this->page_background_color ?: self::DEFAULT_BACKGROUND_COLOR,
        ];
    }

    /**
     * Fotos da página pública, sem carregar as imagens (elas são servidas por SalonPhotoController).
     *
     * @return array{logo: ?array{id: int, url: string}, cover: ?array{id: int, url: string}, gallery: list<array{id: int, url: string}>}
     */
    public function pagePhotos(): array
    {
        $photos = $this->photos()->withoutGlobalScopes()->select(SalonPhoto::SUMMARY_COLUMNS)->orderBy('position')->orderBy('id')->get();
        $props = fn (?SalonPhoto $photo) => $photo ? ['id' => $photo->id, 'url' => $photo->url()] : null;

        return [
            'logo' => $props($photos->firstWhere('kind', PhotoKind::Logo)),
            'cover' => $props($photos->firstWhere('kind', PhotoKind::Cover)),
            'gallery' => $photos->where('kind', PhotoKind::Gallery)->map($props)->values()->all(),
        ];
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

    public function photos(): HasMany
    {
        return $this->hasMany(SalonPhoto::class);
    }
}
