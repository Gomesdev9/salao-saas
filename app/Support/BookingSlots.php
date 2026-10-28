<?php

namespace App\Support;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Salon;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Horários livres para a cliente agendar pela página do salão.
 *
 * Os horários andam de STEP_MINUTES em STEP_MINUTES dentro do funcionamento do dia.
 * Um horário está livre quando algum profissional que faz o serviço não tem nada
 * marcado durante toda a duração dele. As consultas ignoram o filtro por salão do
 * usuário logado, porque a página é pública: tudo é filtrado pelo salão da URL.
 */
class BookingSlots
{
    public const STEP_MINUTES = 30;

    /** Quantos dias, a partir de hoje, a cliente pode escolher. */
    public const DAYS_AHEAD = 14;

    public function __construct(private Salon $salon, private Service $service) {}

    /**
     * Profissionais ativos que fazem o serviço.
     *
     * @return Collection<int, Professional>
     */
    public function professionals(): Collection
    {
        return $this->service->professionals()
            ->withoutGlobalScope('salon')
            ->where('professionals.salon_id', $this->salon->id)
            ->where('professionals.active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Os dias que aparecem para escolher, de hoje em diante.
     *
     * @return list<Carbon>
     */
    public static function days(): array
    {
        return array_map(fn (int $i) => today()->addDays($i), range(0, self::DAYS_AHEAD - 1));
    }

    public static function isBookableDay(Carbon $day): bool
    {
        return $day->betweenIncluded(today(), today()->addDays(self::DAYS_AHEAD - 1));
    }

    /**
     * Horários do dia que ainda não passaram: hora => id do profissional livre (null quando está tudo ocupado).
     * Sem profissional escolhido, fica com quem tem menos horários no dia.
     *
     * @return array<string, int|null>
     */
    public function on(Carbon $day, ?Professional $professional = null): array
    {
        $hours = $this->salon->hoursOn($day);
        $candidates = $professional ? collect([$professional]) : $this->professionals();

        if (! $hours || ! self::isBookableDay($day) || $candidates->isEmpty()) {
            return [];
        }

        $busy = Appointment::withoutGlobalScope('salon')
            ->where('salon_id', $this->salon->id)
            ->whereIn('professional_id', $candidates->pluck('id'))
            ->where('starts_at', '<', $day->copy()->endOfDay())
            ->where('ends_at', '>', $day->copy()->startOfDay())
            ->whereIn('status', AppointmentStatus::occupying())
            ->get(['professional_id', 'starts_at', 'ends_at'])
            ->groupBy('professional_id');

        $candidates = $candidates->sortBy(fn (Professional $p) => $busy->get($p->id)?->count() ?? 0)->values();

        $duration = $this->service->duration_minutes;
        $close = $day->copy()->setTimeFromTimeString($hours[1]);
        $slots = [];

        for ($start = $day->copy()->setTimeFromTimeString($hours[0]); $start->copy()->addMinutes($duration)->lte($close); $start->addMinutes(self::STEP_MINUTES)) {
            if ($start->lte(now())) {
                continue;
            }

            $end = $start->copy()->addMinutes($duration);
            $free = $candidates->first(fn (Professional $p) => ! ($busy->get($p->id) ?? collect())
                ->contains(fn (Appointment $a) => $a->starts_at->lt($end) && $a->ends_at->gt($start)));

            $slots[$start->format('H:i')] = $free?->id;
        }

        return $slots;
    }
}
