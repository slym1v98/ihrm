<?php

namespace App\Modules\Workflow\Application\Services;

use Carbon\CarbonImmutable;

final class WorkflowSlaCalculator
{
    /**
     * @param  array<string, array{start: int, end: int}|null>  $workingHours
     */
    public function calculateDeadline(CarbonImmutable $from, float $businessHours, array $workingHours): CarbonImmutable
    {
        $remainingMinutes = (int) round($businessHours * 60);
        $current = $from;

        while ($remainingMinutes > 0) {
            $dayKey = strtolower($current->format('D'));
            $wh = $workingHours[$dayKey] ?? null;
            if ($wh === null) {
                $current = $current->addDay()->startOfDay();

                continue;
            }
            $dayStart = $current->startOfDay()->addHours($wh['start']);
            $dayEnd = $current->startOfDay()->addHours($wh['end']);

            // If before working hours, jump to start
            if ($current->lt($dayStart)) {
                $current = $dayStart;
            }

            // If after working hours, skip to next day
            if ($current->gte($dayEnd)) {
                $current = $current->addDay()->startOfDay();

                continue;
            }

            $availableToday = (int) $current->diffInMinutes($dayEnd);
            $use = min($remainingMinutes, $availableToday);
            $current = $current->addMinutes($use);
            $remainingMinutes -= $use;
        }

        return $current;
    }
}
