<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;

class ScheduleService
{
    // Duração total (min) para um conjunto de serviços.
    public function durationFor(array $serviceIds): int
    {
        return (int) Service::whereIn('id', $serviceIds)->sum('execution_time');
    }

    // Retorna o primeiro agendamento que se sobrepõe ao intervalo informado, ou null.
    // Considera o tempo de execução de cada serviço. Ignora concluídos e o próprio (ignoreId).
    public function conflict(Carbon $start, int $durationMinutes, ?int $ignoreId = null): ?Appointment
    {
        $end = (clone $start)->addMinutes(max($durationMinutes, 1));

        $sameDay = Appointment::with('services')
            ->whereDate('scheduled_at', $start->toDateString())
            ->where('status', '!=', 'concluido')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get();

        foreach ($sameDay as $appt) {
            $aStart = $appt->scheduled_at->copy();
            $aEnd = $aStart->copy()->addMinutes(max($appt->duration, 1));
            if ($start->lt($aEnd) && $end->gt($aStart)) {
                return $appt;
            }
        }
        return null;
    }
}
