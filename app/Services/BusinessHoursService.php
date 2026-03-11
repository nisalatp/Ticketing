<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class BusinessHoursService
{
    protected $startHour = 9;  // 9 AM
    protected $endHour = 17;   // 5 PM (8 hours)
    protected $workdayHours = 8;

    /**
     * Add business minutes to a date.
     */
    public function addBusinessMinutes(Carbon $start, int $minutes): Carbon
    {
        $date = $start->copy();
        $remainingMinutes = $minutes;

        while ($remainingMinutes > 0) {
            // If it's a weekend, move to Monday morning
            if ($date->isWeekend()) {
                $date->next(CarbonInterface::MONDAY)->setTime($this->startHour, 0);
                continue;
            }

            // If it's before work hours, move to today's start
            if ($date->hour < $this->startHour) {
                $date->setTime($this->startHour, 0);
            }

            // If it's after work hours, move to tomorrow's start
            if ($date->hour >= $this->endHour) {
                $date->addDay()->setTime($this->startHour, 0);
                continue;
            }

            // Calculate minutes left in current workday
            $workdayEnd = $date->copy()->setTime($this->endHour, 0);
            $minutesLeftInDay = $date->diffInMinutes($workdayEnd);

            if ($remainingMinutes <= $minutesLeftInDay) {
                $date->addMinutes($remainingMinutes);
                $remainingMinutes = 0;
            } else {
                $remainingMinutes -= $minutesLeftInDay;
                $date->addDay()->setTime($this->startHour, 0);
            }
        }

        // Final check: if we landed on a weekend or after hours, push to next business window
        while ($date->isWeekend() || $date->hour >= $this->endHour || $date->hour < $this->startHour) {
             if ($date->isWeekend()) {
                $date->next(CarbonInterface::MONDAY)->setTime($this->startHour, 0);
            } elseif ($date->hour >= $this->endHour) {
                $date->addDay()->setTime($this->startHour, 0);
            } elseif ($date->hour < $this->startHour) {
                $date->setTime($this->startHour, 0);
            }
        }

        return $date;
    }

    /**
     * Get elapsed business minutes between two dates.
     */
    public function getBusinessMinutesElapsed(Carbon $start, Carbon $end): int
    {
        if ($start->gt($end)) {
            return 0;
        }

        $current = $start->copy();
        $totalMinutes = 0;

        while ($current->lt($end)) {
            if ($current->isWeekend()) {
                $current->next(CarbonInterface::MONDAY)->setTime($this->startHour, 0);
                if ($current->gt($end)) break;
            }

            if ($current->hour < $this->startHour) {
                $current->setTime($this->startHour, 0);
                if ($current->gt($end)) break;
            }

            if ($current->hour >= $this->endHour) {
                $current->addDay()->setTime($this->startHour, 0);
                continue;
            }

            $dayEnd = $current->copy()->setTime($this->endHour, 0);
            $target = $end->lt($dayEnd) ? $end : $dayEnd;

            $totalMinutes += $current->diffInMinutes($target);
            $current = $target;

            if ($current->hour >= $this->endHour) {
                $current->addDay()->setTime($this->startHour, 0);
            }
        }

        return $totalMinutes;
    }
}
