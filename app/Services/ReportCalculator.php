<?php

namespace App\Services;

use App\Models\Race;
use App\Models\ReportEntry;
use App\Models\ReportDayDsc;
use App\Models\ReportRaceDsc;
use App\Models\ReportDayAdmin;
use App\Models\ReportAdminRaceSettings;

class ReportCalculator
{
    /**
     * Alias "compatibilità".
     */
    public function computeRow(
        Race $race,
        ReportEntry $entry,
        ?ReportRaceDsc $dscRace,
        ?ReportAdminRaceSettings $settings
    ): array {
        return $this->computeRowForRace($race, $entry, $dscRace, $settings);
    }

    /**
     * ✅ Alias per compatibilità con controller/view che chiamano ancora computeRowForDay().
     * In questo progetto "ForDay" qui è usato come "calcoli parte gara (non giornalieri)".
     * $adminAny non serve: lo ignoro.
     */
    public function computeRowForDay(
        Race $race,
        ReportEntry $entry,
        ?ReportRaceDsc $dscRace,
        ?ReportDayAdmin $adminAny,
        ?ReportAdminRaceSettings $settings
    ): array {
        return $this->computeRowForRace($race, $entry, $dscRace, $settings);
    }

    /**
     * Totali "per gara" (una riga per crono).
     */
    public function computeRowForRace(
        Race $race,
        ReportEntry $entry,
        ?ReportRaceDsc $dscRace,
        ?ReportAdminRaceSettings $settings
    ): array {
        $missedMeals = (int) ($dscRace?->missed_meals ?? 0);
        $missedMealsAmount = round($missedMeals * 15, 2);

        $coeff = $settings?->coeff_km !== null ? (float) $settings->coeff_km : 0.36;

        $kmAmount = $this->computeKmAmount($race, $entry, $settings);

        $pedaggi = (float) ($entry->pedaggi ?? 0);
        $vitto = (float) ($entry->vitto ?? 0);
        $alloggio = (float) ($entry->alloggio ?? 0);
        $speseVarie = (float) ($entry->spese_varie ?? 0);

        $vanCost = 0.00;
        if (($dscRace?->van_needed ?? false) && $settings?->van_cost !== null) {
            $vanCost = (float) $settings->van_cost;
        }

        $totalRacePart = round(
            $kmAmount + $pedaggi + $vitto + $alloggio + $speseVarie + $missedMealsAmount + $vanCost,
            2
        );

        return [
            'coeffKm' => round($coeff, 4),
            'kmAmount' => round($kmAmount, 2),

            'missedMeals' => $missedMeals,
            'missedMealsAmount' => $missedMealsAmount,

            'vanCostApplied' => round($vanCost, 2),

            'totalRacePart' => $totalRacePart,
        ];
    }

    /**
     * Importo Km:
     * - se domicilio == luogo gara => 10€
     * - altrimenti km * coeff_km
     */
    public function computeKmAmount(
        Race $race,
        ReportEntry $entry,
        ?ReportAdminRaceSettings $settings
    ): float {
        $coeff = $settings?->coeff_km !== null ? (float) $settings->coeff_km : 0.36;
        $km = $entry->km !== null ? (float) $entry->km : 0.0;

        $domicile = trim(mb_strtolower((string) ($entry->user->domicile ?? '')));
        $racePlace = trim(mb_strtolower((string) ($race->place ?? '')));

        if ($domicile !== '' && $racePlace !== '' && $domicile === $racePlace) {
            return 10.00;
        }

        return round($km * $coeff, 2);
    }

    public function computeMissedMealsAmount(?ReportRaceDsc $dscRace): float
    {
        $missedMeals = (int) ($dscRace?->missed_meals ?? 0);
        return round($missedMeals * 15, 2);
    }

    /**
     * Ore lavorate (per giornata) da orari DSC.
     * ✅ Con la nuova logica, $dscDayHours è la riga per (race + day + user).
     * Se non esiste => 0.
     */
    public function computeWorkedHoursForDay(?ReportDayDsc $dscDayHours): float
    {
        if (!$dscDayHours) {
            return 0.0;
        }

        $m = $this->diffHours($dscDayHours->morning_start, $dscDayHours->morning_end);
        $a = $this->diffHours($dscDayHours->afternoon_start, $dscDayHours->afternoon_end);

        return round($m + $a, 2);
    }

    private function diffHours(?string $start, ?string $end): float
    {
        if (!$start || !$end) {
            return 0.0;
        }

        try {
            $start = trim($start);
            $end = trim($end);

            $formatStart = (substr_count($start, ':') === 2) ? 'H:i:s' : 'H:i';
            $formatEnd = (substr_count($end, ':') === 2) ? 'H:i:s' : 'H:i';

            $s = \Carbon\Carbon::createFromFormat($formatStart, $start);
            $e = \Carbon\Carbon::createFromFormat($formatEnd, $end);

            if ($e->lt($s)) {
                return 0.0;
            }

            return round($s->diffInMinutes($e) / 60, 2);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private function amountOrdinary(float $hours): float
    {
        if ($hours <= 0)
            return 0.0;

        $first = min($hours, 4.0);
        $extra = max($hours - 4.0, 0.0);

        return round(($first * 30.0) + ($extra * 36.0), 2);
    }

    private function amountSpecial(float $hours): float
    {
        if ($hours <= 0)
            return 0.0;

        $first = min($hours, 4.0);
        $extra = max($hours - 4.0, 0.0);

        return round(($first * 40.0) + ($extra * 50.0), 2);
    }

    public function computeServiceTotalForDay(?ReportDayAdmin $adminDayRow): array
    {
        $ordHours = (float) ($adminDayRow?->hours_ordinary_service ?? 0.0);
        $specHours = (float) ($adminDayRow?->hours_special_service ?? 0.0);

        $ordAmount = $this->amountOrdinary($ordHours);
        $specAmount = $this->amountSpecial($specHours);

        $ordRateAvg = $ordHours > 0 ? round($ordAmount / $ordHours, 2) : 0.0;
        $specRateAvg = $specHours > 0 ? round($specAmount / $specHours, 2) : 0.0;

        $total = round($ordAmount + $specAmount, 2);

        return [
            'ordHours' => round($ordHours, 2),
            'specHours' => round($specHours, 2),

            'ordRate' => $ordRateAvg,
            'specRate' => $specRateAvg,

            'ordAmount' => $ordAmount,
            'specAmount' => $specAmount,
            'totalService' => $total,
        ];
    }

    public function computeTotaleCrono(iterable $adminRowsForUser): float
    {
        $sum = 0.0;

        foreach ($adminRowsForUser as $row) {
            $day = $this->computeServiceTotalForDay($row);
            $sum += (float) ($day['totalService'] ?? 0.0);
        }

        return round($sum, 2);
    }

    public function computeGrandTotalForCrono(
        Race $race,
        ReportEntry $entry,
        ?ReportRaceDsc $dscRace,
        ?ReportAdminRaceSettings $settings,
        iterable $adminRowsForUser
    ): array {
        $racePart = $this->computeRowForRace($race, $entry, $dscRace, $settings);
        $totaleCrono = $this->computeTotaleCrono($adminRowsForUser);

        $grandTotal = round(((float) ($racePart['totalRacePart'] ?? 0.0)) + $totaleCrono, 2);

        return [
            'totalRacePart' => (float) ($racePart['totalRacePart'] ?? 0.0),
            'totaleCrono' => $totaleCrono,
            'grandTotal' => $grandTotal,
        ];
    }

    public function computeRaceDaysCount(Race $race): int
    {
        $start = \Carbon\Carbon::parse($race->date_of_race)->startOfDay();
        $end = $race->date_end ? \Carbon\Carbon::parse($race->date_end)->startOfDay() : $start->copy();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        return $start->diffInDays($end) + 1;
    }
}
